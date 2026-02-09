<?php

namespace App\Http\Controllers\Api;

use App\Exports\OrderCustomerExport;
use App\Exports\OrderExport;
use App\Exports\OrderSupplierExport;
use App\Http\Requests\Order\CreateRequest;
use App\Http\Requests\Order\DetailRequest;
use App\Http\Requests\Order\ExportOrderRequest;
use App\Http\Requests\Order\ExportRequest;
use App\Http\Requests\Order\UpdateRequest;
use App\Http\Resources\Order\OrderResource;
use App\Jobs\OrderStorage;
use App\Jobs\OrderStorageCancel;
use App\Jobs\ReceiptPayment;
use App\Jobs\ReportAndPayment;
use App\Jobs\ReportAndPaymentCancel;
use App\Jobs\ReportAndReceipt;
use App\Jobs\ReportAndReceiptCancel;
use App\Jobs\UpdateBaseCost;
use App\Jobs\UpdateBaseCostDelete;
use App\Models\Customer;
use App\Models\CustomerDebtHistory;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Models\Order;
use App\Repositories\OrderDetail\OrderDetailRepositoryInterface;
use App\Repositories\Pagination;
use App\Repositories\ProductStorage\ProductStorageRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class OrderController extends Controller
{
    protected $orderRepo;
    protected $orderDetailRepo;
    protected $productStorageRepo;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderDetailRepositoryInterface $orderDetailRepository,
        ProductStorageRepositoryInterface $productStorageRepository
    ) {
        $this->orderRepo = $orderRepository;
        $this->orderDetailRepo = $orderDetailRepository;
        $this->productStorageRepo = $productStorageRepository;
    }

    public function index(Request $request)
    {
        Log::channel('order')->info('index: ' . json_encode($request->all()));
        try {
            $data = $this->orderRepo->getList($request);
            $data->load('orderDetail.product', 'orderPayment', 'customer');
            Log::info('data: ' . json_encode($data));
            ;
            $paginate = new Pagination($data);
            return $this->response200(
                OrderResource::collection($paginate->getItems()),
                $paginate->getMeta(),
            );
        } catch (\Exception $e) {
            Log::channel('order')->info($e->getMessage());
            return $this->response500($e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            $order = $this->orderRepo->find($id);
            if (!$order) {
                return $this->response404('Không tìm thấy đơn hàng');
            }
            $order->load('orderDetail.product', 'orderPayment', 'customer');
            return $this->response200(new OrderResource($order));
        } catch (\Exception $e) {
            Log::channel('order')->info($e->getMessage());
            return $this->response500($e->getMessage());
        }
    }
    public function create(CreateRequest $request)
    {
        try {
            DB::beginTransaction();
            $payment = $request->get('payment');
            $orderData = $this->orderRepo->getData(request: $request);
            $order = $this->orderRepo->insertData($orderData);

            $now = date('Y-m-d');
            $createdAt = date('Y-m-d', strtotime($order->created_at));

            // Fetch Order Details based on the type
            if ($order->type == Order::PURCHASE_ORDER) {
                $orderDetails = $this->orderDetailRepo->getDataPurchase($request->order_detail, $order);
            } else {
                $orderDetails = $this->orderDetailRepo->getDataSales($request->order_detail, $order);
            }
            Log::info('orderDetails: ' . json_encode($orderDetails));

            $order->base_cost = collect($orderDetails)->sum('base_cost');
            $order->wholesale_cost = collect($orderDetails)->sum('wholesale_cost');
            $order->retail_cost = collect($orderDetails)->sum('retail_cost');
            $order->retail_cost_base = collect($orderDetails)->sum('retail_cost_base');
            $order->base_cost_base = collect($orderDetails)->sum('base_cost_base');
            $order->entry_cost = collect($orderDetails)->sum('entry_cost');

            $order->code = $this->orderRepo->getCodeOrder($order->id);

            $order->payment_status = 1; // Payment status: not paid
            if ($payment) {
                $order->payment_status = 2; // Partially paid
            }
            if ($order->type == Order::RETURN) {
                $order->payment_status = 3; // Returned
            }

            // Calculate additional order details
            $order = $this->__processOrderPrices($order, $payment);

            if (Carbon::now()->format('Y-m-d') == $createdAt) {
                $orderDetails = array_map(function ($detail) {
                    return new \App\Models\OrderDetail($detail);
                }, $orderDetails);

                $order->orderDetail()->saveMany($orderDetails); // Saving the details
            } else {
                $order->timestamps = false;
                $this->orderDetailRepo->__savingWithoutTimestamp($orderDetails, $order);
            }


            $paymentPrice = $payment['price'] ?? 0;
            $debt = (bool) $request->get('is_debt_payment');

            if ($debt == 1 && $paymentPrice > 0) {
                $price_debt = $paymentPrice - $order->retail_cost;
                if (!isset($order->customer)) {
                    return $this->response400('Khách hàng không hợp lệ');
                }
                $customer_total_money = $order->customer->total_money ?? 0;
                if ($price_debt > $customer_total_money || $price_debt < 0) {
                    return $this->response400(
                        'Số tiền thanh toán không được lớn hơn số tiền nợ của khách hàng và phải lớn hơn 0'
                    );
                }

                $new_customerDebt = $customer_total_money - $price_debt;
                Customer::query()->where('id', $order->customer_id)->update(['total_money' => $new_customerDebt]);
                CustomerDebtHistory::query()->create([
                    'user_id' => auth()->id(),
                    'customer_id' => $order->customer_id,
                    'price' => $price_debt,
                    'previous_debt' => $customer_total_money,
                    'remaining_debt' => $new_customerDebt,
                    'note' => 'Thanh toán nợ đơn hàng ' . $order->code,
                ]);

            }

            $order->save();

            // Handle payment if provided


            if ($payment) {
                $isRefund = $order->status == Order::RETURN;
                OrderPayment::query()->create([
                    'order_id' => $order->id,
                    'price' => ($isRefund) ? '-' . $paymentPrice : $paymentPrice,
                    'type' => $payment['type'],
                    'user_id' => auth()->id(),
                ]);
            }

            DB::commit();

            // Dispatch jobs based on order properties
            if ($order->status == Order::SUCCESS || $order->status == Order::RETURN) {
                /*
                    Create storage history
                */
                dispatch(new OrderStorage($order->id, auth()->id(), $request->get('type', 1)));

                /*
                    Update base cost for purchase order
                */
                if ($order->type == 2) {
                    dispatch(new UpdateBaseCost($order->id, auth()->user(), $order->status));
                }
                /*
                    create receipt payment or update report
                */
                if ($now == $createdAt && $payment) {
                    if ($payment['price'] > 0) {
                        dispatch(new ReceiptPayment($order->id, auth()->user(), $paymentPrice));
                    }
                } else {
                    //update report nếu tạo đơn chọn ngày trước ngày hiện tại
                    if ($order->type == 1) {
                        dispatch(new ReportAndReceipt($order->id, auth()->user(), $paymentPrice));
                    } else {
                        dispatch(new ReportAndPayment($order->id, auth()->user(), $paymentPrice));
                    }
                    //end
                }
            }

            return $this->response200($order->refresh());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('order')->info($e->getMessage());
            return $this->response500($e->getMessage());
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        Log::channel('order')->info('update: ' . json_encode($request->all()));
        try {
            $data = $request->validated();
            $order = $this->orderRepo->find($id);
            if (
                $order->status == Order::SUCCESS
                || $order->status == Order::RETURN
                || $order->status == Order::CANCELLED
            ) {
                return $this->response422('Đơn hàng này không được cập nhật');
            }
            if (isset($data['vat']) && $data['vat'] == null) {
                unset($data['vat']);
            }
            $is_edit = false;
            if (
                (isset($data['discount']) && $data['discount'] != $order->discount)
                || isset($data['discount_type']) && $data['discount_type'] != $order->discount_type
            ) {
                $is_edit = true;
            }

            if (!isset($data['discount']) || !$data['discount']) {
                $data['discount'] = 0;
            }
            $order->load('orderDetail');
            DB::beginTransaction();


            $payment = $request->get('payment');

            if (isset($data['create_date']) && !$data['create_date']) {
                $data['create_date'] = date('Y-m-d H:i:s');
            }

            if ($request->get('create_date')) {
                $data['created_at'] = $request->get('create_date');
                $data['updated_at'] = $request->get('create_date');
                $data['timestamps'] = false;
            } else {
                $data['create_date'] = date('Y-m-d H:i:s');
            }

            $order = $this->orderRepo->updateById($data, $order);

            if ($order->status == Order::CANCELLED) {
                $this->orderDetailRepo->cancelDataUpdate($request->order_detail, $order);
            } else {
                if ($request->get('order_detail') || $is_edit) {
                    if ($request->get('order_detail')) {
                        if ($order->type == 2) {
                            $this->orderDetailRepo->getDataUpdate($request->order_detail, $order);
                        } else {
                            $orderDetailData = $this->orderDetailRepo->getDataUpdateSales($request->order_detail, $order);
                            if (isset($orderDetailData['code'])) {
                                return $this->response422($orderDetailData['msg']);
                            }
                        }
                        $orderDetailData = $this->orderDetailRepo->findByField('order_id', $order->id);

                        $order->wholesale_cost = collect($orderDetailData)->sum('wholesale_cost');
                        $order->base_cost = collect($orderDetailData)->sum('base_cost');
                        $order->retail_cost = collect($orderDetailData)->sum('retail_cost');
                        $order->retail_cost_base = collect($orderDetailData)->sum('retail_cost_base');
                        $order->base_cost_base = collect($orderDetailData)->sum('base_cost_base');
                        $order->entry_cost = collect($orderDetailData)->sum('entry_cost');
                    }
                }

                $order = $this->__processOrderPrices($order, $payment);
                $order->save();


                if ($payment) {
                    OrderPayment::query()->where('order_id', $order->id)->delete();
                    OrderPayment::query()->create([
                        'order_id' => $order->id,
                        'price' => $payment['price'] ?? 0,
                        'type' => $payment['type'],
                        'user_id' => auth()->id(),
                    ]);

                    if ($order->type == 2 && $order->base_cost > @$payment['price']) {
                        $order->payment_status = 2;
                    } else if ($order->type == 2 && $order->base_cost <= @$payment['price']) {
                        $order->payment_status = 3;
                    } else if ($order->retail_cost > @$payment['price']) {
                        $order->payment_status = 2;
                    } else if ($order->retail_cost <= @$payment['price']) {
                        $order->payment_status = 3;
                    }
                    $order->save();
                }
            }
            DB::commit();
            if ($order->status == Order::SUCCESS) {
                // upsert receipt payment
                if ($order->type == 1) {
                    $this->__updateProduct($order->fresh());
                }

                dispatch(new OrderStorage($order->id, auth()->user(), $request->get('type', 1), 'update'));

                if ($payment && $payment['price'] > 0) {
                    dispatch(new ReceiptPayment($order->id, auth()->user(), $payment['price']));
                }

                if ($order->type == 2) {
                    dispatch(new UpdateBaseCost($order->id, auth()->user(), $order->status));
                }
            }

            return $this->response200();

        } catch (\Exception $e) {
            Log::channel('order')->info($e->getMessage());
            return $this->response500($e->getMessage());
        }
    }
    private function __processOrderPrices($order, $payment)
    {
        $isPurchaseOrder = $order->type == 2;
        $isReturnOrder = $order->status == Order::RETURN;
        $cost = $isPurchaseOrder ? 'base_cost' : 'retail_cost';

        // Apply discount
        if ($order->discount > 0) {
            if ($order->discount_type == 1) { // Percentage discount
                $order->$cost -= ($order->$cost * $order->discount / 100);
                if ($isReturnOrder) {
                    $order->discount_refund = $order->discount;
                }
            } else { // Fixed discount
                if ($isReturnOrder) {
                    // Handle return with fixed discount
                    $order->$cost *= -1;
                    $oldCost = $isPurchaseOrder
                        ? $this->orderRepo->getTotalBaseCostByOrderId($order->order_id)
                        : $this->orderRepo->getTotalRetailCostByOrderId($order->order_id);

                    $percentOfOrder = $order->$cost / $oldCost;
                    $discountOfOrder = $order->discount * $percentOfOrder;
                    $order->$cost -= $discountOfOrder;
                    $order->discount_refund = $discountOfOrder;
                    $order->$cost *= -1;
                } else {
                    $order->$cost -= $order->discount;
                }
            }
        }

        // Apply VAT
        if ($order->vat > 0) {
            $vatMultiplier = $order->vat / 100;
            $order->$cost += $order->$cost * $vatMultiplier;
            // $order->service_fee += $order->service_fee * $vatMultiplier;
        }

        // Check payment status
        $totalCost = $order->$cost + $order->service_fee;
        $paymentAmount = $payment['price'] ?? 0;

        if ($totalCost <= $paymentAmount) {
            $order->payment_status = 3; // Fully paid
        }

        return $order;
    }
    private function __updateProduct($order)
    {
        foreach ($order->orderDetail ?? [] as $item) {
            $product = Product::query()->find($item['product_id']);
            if (!$product)
                continue;

            $quantity = $item['quantity'];

            if ($order->type == 1) { // Sales order
                if ($order->status == Order::SUCCESS) {
                    $product->available -= $quantity;
                    $product->sold += $quantity;
                } elseif ($order->status == Order::CANCELLED) {
                    $product->temporality += $quantity;
                }
            } elseif ($order->status == Order::SUCCESS) { // Purchase order success
                $product->available += $quantity;
                $product->temporality += $quantity;
                $product->in_stock += $quantity;
            }

            $product->save();
        }
    }
    public function cancel(Request $request, $id)
    {
        Log::channel('order')->info('cancel: ' . json_encode($request->all()));
        try {
            $order = $this->orderRepo->find($id);
            if (!$order) {
                return $this->response404('Không tìm thấy đơn hàng');
            }
            $order->load('orderPayment');

            if ($order->status != Order::SUCCESS) {
                return $this->response422('ID đơn hàng không được cập nhật');
            }
            $now = date('Y-m-d');
            $updatedAt = date('Y-m-d', strtotime($order->updated_at));
            DB::beginTransaction();
            $pricePayment = $order->orderPayment->sum('price');
            $price = $order->base_cost;

            if ($now == $updatedAt) {
                $order->status = Order::CANCELLED;
                \App\Models\ReceiptPayment::query()->where('order_id', $order->id)->delete();
            } else {
                $order->status = Order::CANCEL_SUCCESS;

                if ($order->type == 1) {
                    $price = $order->retail_cost;
                }
            }

            $order->save();

            if ($order->type == 1) {
                foreach ($order->orderDetail ?? [] as $item) {
                    $product = Product::query()->find($item['product_id']);
                    if ($product) {
                        $product->temporality += $item['quantity'];
                        $product->available += $item['quantity'];
                        $product->sold -= $item['quantity'];
                        $product->save();
                    }
                }
            } else {
                foreach ($order->orderDetail ?? [] as $item) {
                    $product = Product::query()->find($item['product_id']);
                    if ($product) {
                        $product->temporality -= $item['quantity'];
                        $product->available -= $item['quantity'];
                        $product->save();
                    }
                }
            }

            if ($order->status == Order::CANCEL_SUCCESS) {
                if ($pricePayment < $price) {
                    $price = $pricePayment; // check tránh làm tròn số lẻ khi thanh toán nhiều hơn số tiền thực
                }

                if ($order->type == 1) {
                    dispatch(new ReportAndReceiptCancel($order->id, auth()->user(), $price, $updatedAt));
                } else {
                    dispatch(new ReportAndPaymentCancel($order->id, auth()->user(), $price, $updatedAt));
                }
            }
            if ($order->type == 2) {
                dispatch(new UpdateBaseCostDelete($order, auth()->user()));
            }
            dispatch(new OrderStorageCancel($order->id, auth()->user(), $request->get('type', 1)));
            DB::commit();
            return $this->response200();
        } catch (\Exception $e) {
            Log::channel('order')->info($e->getMessage());
            return $this->response500($e->getMessage());
        }
    }
    public function delete(Request $request, $id)
    {
        Log::channel('order')->info("Delete :" . json_encode($request->all()) . ' ' . $id);
        try {
            $order = $this->orderRepo->find($id);
            if (!$order) {
                return $this->response200();
            }
            $updatedAt = date('Y-m-d', strtotime($order->updated_at));

            $order->load('orderDetail.product', 'orderRefund');

            $order->active = false;
            $order->save();

            if ($order->status == Order::PENDING) {
                if ($order->type == 1) {
                    foreach ($order->orderDetail ?? [] as $item) {
                        $product = Product::query()->find($item['product_id']);
                        if ($product) {
                            $product->temporality += $item['quantity'];
                            $product->save();
                        }
                    }
                }
                return $this->response200();
            }
            $this->__delete($request, $order, $updatedAt);
            return $this->response200();
        } catch (\Exception $exception) {
            Log::channel('order')->info($exception);
            return $this->response500('delete: ' . $exception->getMessage());
        }
    }

    private function __delete(Request $request, $order, $updatedAt)
    {
        $pricePayment = $order->orderPayment->sum('price');
        $now = date('Y-m-d');

        $price = $order->type == 1 ? $order->retail_cost : $order->base_cost;
        foreach ($order->orderDetail ?? [] as $item) {
            $product = Product::query()->find($item['product_id']);
            if ($product) {
                $quantity = $item['quantity'];
                $product->temporality += $order->type == 1 ? $quantity : -$quantity;
                $product->available += $order->type == 1 ? $quantity : -$quantity;
                if ($order->type == 1) {
                    $product->sold -= $quantity;
                } else {
                    $product->in_stock -= $quantity;
                }
                $product->save();
            }
        }

        if ($now == $updatedAt) {
            \App\Models\ReceiptPayment::query()->where('order_id', $order->id)->delete();
        } else {
            if ($pricePayment < $price) {
                $price = $pricePayment; // check tránh làm tròn số lẻ khi thanh toán nhiều hơn số tiền thực
            }

            if ($order->type == 1) {
                // dispatch(new ReportAndReceiptDelete($order, auth()->user(), $price, $updatedAt));
            } else {
                // dispatch(new ReportAndPaymentDelete($order, auth()->user(), $price, $updatedAt));
            }
        }

        if ($order->type == 2 && $order->status == Order::SUCCESS) {
            // dispatch(new UpdateBaseCostDelete($order, auth()->user()));
        }

        // dispatch(new OrderStorageDelete($order, auth()->user(), $request->get('type', 1)));

    }
    public function payment(Request $request, $id)
    {
        Log::channel('order')->info('pay: ' . json_encode($request->all()));
        try {
            $order = $this->orderRepo->find($id);
            if (!$order) {
                return $this->response404('Không tìm thấy đơn hàng');
            }
            $order->load('orderPayment');
            $paymentCost = $order->orderPayment->sum('price');
            if ($order->type != 2) {
                $cost = $order->retail_cost;
            } else {
                $cost = $order->base_cost;
            }
            DB::beginTransaction();
            if ($cost <= ($paymentCost + $request->get('price', 0))) {
                $order->payment_status = 3; // đã thanh toán
                $order->timestamps = false;
                $order->save();
            }

            $payment = OrderPayment::query()->create([
                'order_id' => $id,
                'price' => $request->get('price', 0),
                'type' => $request->type,
                'user_id' => auth()->id(),
            ]);
            DB::commit();

            if ($order->status == Order::SUCCESS) {
                if ($payment->price > 0) {
                    dispatch(new ReceiptPayment($order->id, auth()->user(), $payment->price));
                }
            }
            return $this->response200();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::channel('order')->info($exception);
            return $this->response500('payment: ' . $exception->getMessage());
        }
    }

    public function print(DetailRequest $request, $id)
    {
        $order = Order::query()->with(['orderDetail.product', 'orderPayment'])->find($request->id);
        $userId = auth()->id();
        $debt = $this->orderRepo->debtByPhone($order->customer_id, $order->updated_at);
        Log::info('order' . json_encode($order));

        if (!$order) {
            return $this->response404('Không tìm thấy đơn hàng');
        }
        // customer
        $customerDebt = null;
        if ($order->customer_id) {
            //            $customerDebt = CustomerDebtHistory::query()->where('customer_id', $order->customer_id)->orderByDesc('id')->first();
            $customerDebt = $debt;
        } else {
            $customerDebt = null;
        }

        // QR code link to Zalo
        $qrCode = QrCode::encoding('UTF-8')->size(150)->generate('https://zalo.me/0888472589');

        $dataPrint = [
            'order' => $order,
            'qrCode' => $qrCode,
            'customer_debt' => $customerDebt
        ];

        return view('Order.print_order', $dataPrint);
    }

    public function qr_code($order)
    {
        $total = 0;
        $total_temp = 0;
        $price_product = 0;


        if ($order->type == 1) {
            $total = $order->retail_cost;
            $total_temp = $order->retail_cost_base;
        } else {
            $total = $order->base_cost;
            $total_temp = $order->base_cost_base;
        }

        $lines = [];
        $lines[] = '--- HÓA ĐƠN BÁN HÀNG ---';
        $lines[] = 'Mã đơn hàng: ' . ($order->code ?? 'N/A');
        $lines[] = 'Ngày: ' . ($order->created_date ?? $order->created_at ?? now());

        $lines[] = "\n--- Sản phẩm ---";

        foreach ($order->orderDetail ?? [] as $item) {
            $productName = $item->product->name ?? 'N/A';
            if ($order->type == 1) {
                $price = number_format($item->product->retail_cost, 0, ',', '.') . 'đ';
                $price_product = $item->product->retail_cost;
            } else {
                $price = number_format($item->product->base_cost, 0, ',', '.') . 'đ';
                $price_product = $item->product->base_cost;
            }
            $quantity = $item->quantity;

            $lines[] = "- {$productName} : {$price} x {$quantity} = " . number_format($price_product * $quantity, 0, ',', '.') . 'đ';
        }

        $lines[] = "\nTạm tính: " . number_format($total_temp, 0, ',', '.') . ' đ';

        if ($order->discount_type == 2) {
            $lines[] = 'Giảm giá: ' . $order->discount . ' %';
            $discount = $total_temp * $order->discount / 100;
        } else {
            $lines[] = 'Giảm giá: ' . number_format($order->discount, 0, ',', '.') . ' đ';
            $discount = $order->discount;
        }

        if ($order->vat) {
            $lines[] = 'VAT: ' . $order->vat . '%';
        }

        $lines[] = 'Thành tiền: ' . number_format($total, 0, ',', '.') . ' đ';
        $lines[] = '-------------------------';

        return implode("\n", $lines);
    }

    public function exportOrder(ExportRequest $request)
    {
        $date = $request->validated();
        if ($date['type']) {
            $type = $date['type'];
        } else {
            $type = 1;
        }
        $user = User::query()->where('id', auth()->id())->first();
        $query = Order::query()->with(['orderDetail', 'orderPayment'])
            ->where('active', 1)
            ->where('type', $type);

        build_query_by_user_id($query, $user);

        if (!$date['from'] || !$date['to']) {
            $date['from'] = now()->startOfMonth();
            $date['to'] = now();
        }
        if ($date['from'] > $date['to']) {
            return $this->response404('Ngày bắt đầu không được lớn hơn ngày kết thúc');
        }

        if ($date['from'] && $date['to']) {
            $query->whereBetween('created_at', [$date['from'], $date['to']]);
        }
        $orders = $query->get();
        $dataPrint = [
            'orders' => $orders,
            'user' => $user,
            'from' => $date['from'],
            'to' => $date['to'],
            'type' => $type,
        ];

        return Excel::download(new OrderExport($dataPrint), 'don_hang.xlsx');
    }

    public function exportCustomerOrder(ExportOrderRequest $request, $id)
    {
        try {
            $request = $request->validated();
            $from = isset($request['from']) ? Carbon::parse($request['from']) : null;
            $to = isset($request['to']) ? Carbon::parse($request['to']) : null;
            if (!$from || !$to) {
                $from = now()->startOfMonth();
                $to = now();
            }
            if ($from->gt($to)) {
                return $this->response404('Ngày bắt đầu không được lớn hơn ngày kết thúc');
            }

            $user = auth()->user();
            $customer = Customer::query()->where('id', $id)->where('status', 1)->first();

            if (!$customer) {
                return $this->response404('Khách hàng không tại hoặc đã xóa');
            }
            $query = Order::query()->with(['orderDetail', 'orderPayment'])
                ->where('active', 1)
                ->where('type', 1)
                ->where('customer_id', $id);
            build_query_by_user_id($query, $user);

            $query->whereBetween('created_at', [$from, $to]);
            $orders = $query->get();
            $dataPrint = [
                'orders' => $orders,
                'user' => $user,
                'customer' => $customer,
                'from' => $from,
                'to' => $to,
            ];

            return Excel::download(new OrderCustomerExport($dataPrint), 'chi_tiet_don_hang_khach_hang.xlsx');

        } catch (\Exception $exception) {
            return $this->response500($exception->getMessage());
        }

    }

    public function exportSupplierOrder(ExportOrderRequest $request, $id)
    {
        try {
            $data = $request->validated();
            Log::info('data' . json_encode($data));
            $from = isset($data['from']) ? Carbon::parse($request['from']) : null;
            $to = isset($data['to']) ? Carbon::parse($request['to']) : null;

            Log::info('from' . json_encode($from));
            Log::info('to' . json_encode($to));
            if (!$from || !$to) {
                $from = now()->startOfMonth();
                $to = now();
            }
            if ($from->gt($to)) {
                return $this->response404('Ngày bắt đầu không được lớn hơn ngày kết thúc');
            }

            $user = auth()->user();
            $supplier = Supplier::query()->where('id', $id)->where('status', 1)->first();

            if (!$supplier) {
                return $this->response404('Nhà cung cấp không tại hoặc đã xóa');
            }
            $query = Order::query()->with(['orderDetail', 'orderPayment'])
                ->where('active', 1)
                ->where('type', 2)
                ->where('supplier_id', $id);
            build_query_by_user_id($query, $user);

            $query->whereBetween('created_at', [$from, $to]);
            $orders = $query->get();
            $dataPrint = [
                'orders' => $orders,
                'user' => $user,
                'supplier' => $supplier,
                'from' => $from,
                'to' => $to,
            ];

            return Excel::download(new OrderSupplierExport($dataPrint), 'chi_tiet_don_hang_nha_cung_cap.xlsx');

        } catch (\Exception $exception) {
            return $this->response500($exception->getMessage());
        }

    }

    /**
     * Change customer for an order
     * This allows updating customer info even for completed orders
     */
    public function changeCustomer(Request $request, $id)
    {
        Log::channel('order')->info('changeCustomer: ' . json_encode($request->all()));
        
        try {
            $request->validate([
                'customer_id' => 'required|integer|exists:customers,id',
            ]);

            DB::beginTransaction();
            
            $order = $this->orderRepo->find($id);
            
            if (!$order) {
                return $this->response404('Không tìm thấy đơn hàng');
            }

            // Check if order belongs to current user
            $user = auth()->user();
            if ($order->user_id !== $user->id) {
                return $this->response403('Bạn không có quyền cập nhật đơn hàng này');
            }

            // Get customer info
            $customer = Customer::find($request->customer_id);
            
            if (!$customer || $customer->status == 0) {
                return $this->response404('Không tìm thấy khách hàng hoặc khách hàng đã bị xóa');
            }

            // If order has debt, need to update customer debt
            if ($order->debt > 0) {
                // Remove debt from old customer
                if ($order->customer_id) {
                    $oldCustomer = Customer::find($order->customer_id);
                    if ($oldCustomer) {
                        $oldCustomer->total_money = max(0, $oldCustomer->total_money - $order->debt);
                        $oldCustomer->save();
                    }
                }
                
                // Add debt to new customer
                $customer->total_money += $order->debt;
                $customer->save();
            }

            // Update order with new customer info
            $updateData = [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone ?? $order->phone,
                'email' => $customer->email ?? $order->email,
                'address' => $customer->address ?? $order->address,
            ];

            $this->orderRepo->update($id, $updateData);
            
            DB::commit();
            
            return $this->response200([
                'message' => 'Đổi khách hàng thành công',
                'order' => $this->orderRepo->find($id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('order')->error('changeCustomer error: ' . $e->getMessage());
            return $this->response500($e->getMessage());
        }
    }
}




