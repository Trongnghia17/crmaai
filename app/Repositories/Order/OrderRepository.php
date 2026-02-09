<?php

namespace App\Repositories\Order;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Repositories\BaseRepository;
use App\Repositories\Pagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    protected $modelDetail;

    public function __construct()
    {
        parent::__construct();
        // $this->modelDetail = new OrderDetail();
    }

    public function getModel()
    {
        return Order::class;
    }

    public function getList(Request $request)
    {
        $size = $request->get('per_page', 20);
        $query = $this->model->with([
            'user',
            'supplier',
            'customer',
            'orderPayment',
            'orderRefund',
            'orderDetail.product',
        ]);
        $this->__buildQuery($request, $query);
        build_query_by_user_id($query, auth()->user());

        build_query_sort_field($query, $request->get('sort', 'updated_at'));
        //        Log::info('Query result', $query->paginate($size)->toArray());
        return $query->paginate($size);

    }

    private function __buildQuery($request, &$query): void
    {
        $productId = $request->get('product_ids');
        $phone = $request->get('phone');
        $customerId = $request->get('customer_id');
        $supplierId = $request->input('supplier_id');

        if ($supplierId || $supplierId == "null") {
            if ($supplierId === "null") {
                $query->whereNull('supplier_id');
            } else {
                $query->where('supplier_id', $supplierId);
            }
        }

        if ($phone || $phone == "null") {
            if ($phone === "null") {
                $query->whereNull('phone');
            } else {
                $query->where('phone', $phone);
            }
        }

        if ($customerId || $customerId == "null") {
            if ($customerId === "null") {
                $query->whereNull('customer_id');
            } else {
                $query->where('customer_id', $customerId);
            }
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        } else {
            $query->where('type', 1);
        }

        if ($productId) {
            $query->whereHas('orderDetail', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            });
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('updated_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }
        if ($request->get('search')) {
            $query->where(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                })->orWhereHas('customer', function ($query) use ($request) {
                    $query->where('name', 'like', "%$request->search%");
                })->orWhereHas('supplier', function ($query) use ($request) {
                    $query->where('name', 'like', "%$request->search%");
                });
            });
        }
        if ($request->has('status')) {
            if ($request->status == 3) {
                // Khi chọn "Đã hủy", hiển thị cả status 3 và 5
                $query->whereIn('status', [
                    Order::CANCELLED,
                    Order::CANCEL_SUCCESS
                ]);
            } else {
                // Lọc theo status cụ thể (1, 2, 4, 5)
                $query->where('status', $request->status);
            }
        }
    }

    public function getData($request)
    {
        $data = [
            'user_id' => $request->user_id ?? auth()->id(),
            'customer_id' => $request->customer_id,
            'supplier_id' => $request->supplier_id,
            'phone' => $request->phone,
            'type' => $request->get('type', 1),
            'customer_name' => $request->customer_name,
            'email' => $request->email,
            'status' => $request->get('status', 2),
            'order_id' => $request->order_id,
            'discount_type' => $request->get('discount_type', 1),
            'discount' => $request->discount,
            'vat' => $request->vat,
            'is_retail' => $request->get('is_retail', true),
            'active' => $request->get('active', 1),
            'create_date' => $request->create_date,
        ];
        if ($request->get('create_date')) {
            $data['created_at'] = $request->get('create_date');
            $data['updated_at'] = $request->get('create_date');
            $data['timestamps'] = false;
        }
        return $data;
    }

    public function insertData($data)
    {
        $object = new Order();
        foreach ($data as $key => $value) {
            $object->{$key} = $value;
        }
        $object->save();
        return $object;
    }

    static public function getCodeOrder($orderId)
    {
        $query = DB::table('orders');
        build_query_by_user_id($query, auth()->user());
        $codeCount = $query->count();
        $codeText = 'TH';
        return $codeText . str_pad("{$codeCount}", 5, '0', STR_PAD_LEFT);
    }

    public function getTotalBaseCostByOrderId($orderId)
    {
        return $this->modelDetail->where('order_id', $orderId)->sum('base_cost');
    }
    public function getTotalRetailCostByOrderId($orderId)
    {
        return $this->modelDetail->where('order_id', $orderId)->sum('retail_cost');
    }

    public function updateById($data, $order)
    {
        unset($data['id']);
        if (isset($data['order_detail'])) {
            unset($data['order_detail']);
        }
        foreach ($data ?? [] as $k => $item) {
            $order->{$k} = $item;
        }
        $order->save();

        return $order;
    }

    public function debt(Request $request, $type = 'page')
    {
        $query = $this->model->with(['orderPayment', 'supplier', 'orderDetail.product'])
            ->where(function ($query) use ($request) {
                if (
                    $request->has('type')
                    && $request->get('type') == 2
                ) {
                    $query->where('type', 2);
                } else {
                    $query->where('type', 1);
                }
            })
            ->where('status', "!=", 3);

        if ($request->name) {
            $query->where(function ($query) use ($request) {
                $name = $request->name;
                $query->whereHas('orderDetail.product', function ($query) use ($name) {
                    $query->where('name', 'like', "%$name%");
                })->orWhere('name', 'like', "%$name%");
            });
        }

        if ($request->supplier_id) {
            if ($request->supplier_id == -1) {
                $query->whereNull('supplier_id');
            } else {
                $query->where('supplier_id', $request->supplier_id);
            }
        }

        if ($request->phone) {
            if ($request->phone == -1) {
                $query->whereNull('phone');
            } else {
                $query->where('phone', 'like', "%$request->phone%");
            }
        }

        if ($request->customer_id) {
            if ($request->customer_id == -1) {
                $query->whereNull('customer_id');
            } else {
                $query->where('customer_id', $request->customer_id);
            }
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('updated_at', [
                $request->get('start_date') . ' 00:00:00',
                $request->get('end_date') . ' 23:59:59'
            ]);
        }

        build_query_by_user_id($query, auth()->user());

        $query->whereIn('status', [Order::SUCCESS])
            ->where('status', '!=', 3)
            ->orderByDesc('id');

        if ($type === 'all') {
            return $query->get();
        } else {
            return $query
                ->paginate($request->get('per_page', PAGINATION_ITEMS));
        }
    }

    public function customer_report(Request $request)
    {
        $query = Customer::query()
            ->with([
                'order' => function ($query) use ($request) {
                    if ($request->start_date && $request->end_date) {
                        $query
                            ->whereBetween('updated_at', [
                                $request->start_date . ' 00:00:00',
                                $request->end_date . ' 23:59:59',
                            ]);
                    }
                    $query->with(['orderPayment']);
                }
            ])
            ->where('status', 1);
        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('name', 'like', "%$request->search%")
                    ->orWhere('phone', 'like', "%$request->search%");
            });
        }

        if (!$request->boolean('is_full')) {
            $query->whereHas('order', function ($query) use ($request) {
                $query->where('status', '!=', 3);
            });
        }

        if ($request->start_date && $request->end_date) {
            $query->whereHas('order', function ($query) use ($request) {
                $query->whereBetween('updated_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59',
                ]);
            });
        }

        build_query_by_user_id_with_table_name($query, auth()->user(), 'customers');

        $result = $query->get();

        $data = [];
        foreach ($result->toArray() ?? [] as $key => $value) {
            $data[$key]['customer'] = [
                'name' => @$value['name'],
                'phone' => @$value['phone'],
                'id' => $value['id'],
                'debt_old' => $value['debt_old'] ?? 0,
                'pay_debt_old' => $value['pay_debt_old'] ?? 0,
            ];

            $orderCollect = collect($value['order'])
                ->filter(function ($item) {
                    return $item['status'] === Order::SUCCESS
                        || $item['status'] === Order::RETURN;

                });

            $retailCost = $orderCollect->sum('retail_cost');

            $orderPayment = $orderCollect->sum(function ($order) {
                $retailCostOrder = $order['status'] == 4 ? -$order['retail_cost'] : $order['retail_cost'];
                $totalPayment = 0;

                foreach ($order['order_payment'] as $payment) {
                    $remaining = $retailCostOrder - $totalPayment;
                    $amountToAdd = min($payment['price'], $remaining);
                    if ($amountToAdd < 0)
                        break;
                    $totalPayment += $amountToAdd;
                }

                return $totalPayment;
            });
            $total = $retailCost + $value['debt_old'] ?? 0;

            $data[$key]['number'] = count(collect($value['order'])->filter(function ($item) {
                return
                    ($item['status'] === Order::SUCCESS
                        || $item['status'] === Order::RETURN)

                    && $item['status'] != 3;
            }));
            $data[$key]['total_pay'] = $orderPayment + $value['pay_debt_old'] ?? 0;
            $data[$key]['total'] = $total;

            if (
                ($data[$key]['total_pay'] == 0
                    && $data[$key]['total'] == 0
                    && $data[$key]['number'] == 0)
                || ($data[$key]['total_pay'] >= $data[$key]['total'])
            ) {
                unset($data[$key]);
            }
        }
        return $data;

    }

    public function revenueByCustomerNull(Request $request)
    {
        $query = $this->model
            ->with(['orderPayment'])
            ->whereNull('customer_id')
            ->where(function ($query) {
                $query->where('type', 1)->orWhere('type', 3);
            })
            ->where('active', 1)
            ->where(function ($query) {
                $query->where('status', Order::SUCCESS)
                    ->orWhere('status', Order::RETURN);
            });

        if (!$request->boolean('is_full')) {
            $query->where('status', '!=', 3);
        }

        if ($request->name) {
            $query->whereHas('customer', function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', "%$request->name%")
                        ->orWhere('phone', 'like', "%$request->name%");
                });
            });
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('updated_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        build_query_by_user_id($query, auth()->user());

        $number = $query->count();
        $result = $query->get();

        $orderPayment = $result->sum(fn($order) => $order->orderPayment->sum('price'));
        return [
            'number' => $number,
            'total' => $query->sum('retail_cost'),
            'total_pay' => $orderPayment,
            "customer" => [
                "name" => "Khách lẻ",
                "id" => -1
            ]
        ];
    }

    public function supplier(Request $request)
    {
        $query = \DB::table('orders')
            ->leftJoin('order_payment', 'orders.id', '=', 'order_payment.order_id')
            ->select(
                'orders.supplier_id',
                'orders.id',
                //                \DB::raw('sum(order.base_cost) as total'),
//                \DB::raw('count(order.id) as number'),
                \DB::raw('sum(order_payment.price) as total_pay'),
            )
            ->where('orders.type', 2)
            ->where('orders.active', 1)
            //            ->whereNotNull('order.supplier_id')
            ->where(function ($query) {
                $query->whereIn('orders.status', [Order::SUCCESS, Order::RETURN]);
            });

        build_query_by_user_id_with_table_name($query, auth()->user(), 'orders');
        if ($request->name) {
            $query->join('suppliers', 'orders.supplier_id', '=', 'suppliers.id')
                ->where(function ($query) use ($request) {
                    $query->where('suppliers.name', 'like', "%$request->name%")
                        ->orWhere('suppliers.phone', 'like', "%$request->name%")
                        ->orWhere('suppliers.email', 'like', "%$request->email%");
                });
        }
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('orders.updated_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        return $query->orderByDesc('orders.supplier_id')->groupBy('orders.supplier_id', 'orders.id')->get();
    }

    public function debtByPhone($customerId = null, $dateOrder = null)
    {
        if (!$customerId) {
            return 0;
        }

        $query = $this->model
            ->with(['orderPayment'])
            ->where(function ($query) {
                $query->where('type', 1)->orWhere('type', 3);
            })
            ->where('customer_id', $customerId)
            ->where(function ($query) {
                $query->where('status', 2)->orWhere('status', 4);
            })
            ->where('status', '!=', 3);
        if ($dateOrder) {
            $query = $query->where('created_at', '<=', $dateOrder);
        }
        build_query_by_user_id($query, auth()->user());


        $result = $query->get();

        $cost = collect($result)->sum('retail_cost');
        //        $totalPayment = collect($result)->sum(fn($order) => $order->orderPayment->sum('price'));
        $totalPayment = collect($result)->sum(function ($order) {
            $retailCost = $order->status == 4 ? -$order->retail_cost : $order->retail_cost;
            $paid = $order->orderPayment->sum('price');
            return min($paid, $retailCost); // Không vượt quá giá trị đơn
        });
        return ($cost - $totalPayment);
    }

    public function debtBySupplier($supplierId = null)
    {
        $query = $this->model
            ->with(['orderPayment'])
            ->where('type', 2)
            ->where('supplier_id', $supplierId)
            ->where(function ($query) {
                $query->whereIn('status', [Order::SUCCESS, Order::RETURN]);
            })
            ->where('status', '!=', 3);

        build_query_by_user_id($query, auth()->user());

        $result = $query->get();

        $cost = collect($result)->sum('base_cost');
        //        $totalPayment = collect($result)->sum(fn($order) => $order->orderPayment->sum('price'));
        $totalPayment = collect($result)->sum(function ($order) {
            $retailCost = $order->status == 4 ? -$order->retail_cost : $order->retail_cost;
            $paid = $order->orderPayment->sum('price');
            return min($paid, $retailCost); // Không vượt quá giá trị đơn
        });

        return $cost - $totalPayment;
    }

    public function totalOrderByPhone($customerId = null)
    {
        if (!$customerId) {
            return 0;
        }
        $query = $this->model
            ->where('customer_id', $customerId)
            ->where('status', 2)
            //            ->where('status', '!=', 3)
            ->where(function ($query) {
                $query->where('type', 1)->orWhere('type', 3);
            });

        build_query_by_user_id($query, auth()->user());

        return $query->count();
    }

    public function totalByPhone($customerId = null)
    {
        if (!$customerId) {
            return 0;
        }
        $query = $this->model
            ->where(function ($query) {
                $query->where('type', 1)->orWhere('type', 3);
            })
            ->where('customer_id', $customerId)
            ->where(function ($query) {
                $query->where('status', 2)->orWhere('status', 4);
            });

        build_query_by_user_id($query, auth()->user());
        $result = $query->get();

        $cost = collect($result)->sum('retail_cost');

        return $cost;
    }

    public function totalOrderBySupplier($supplierId = null)
    {
        $query = $this->model
            ->where('supplier_id', $supplierId)
            ->where('status', 2)
            //            ->where('status', '!=', 3)
            ->where('type', 2);

        build_query_by_user_id($query, auth()->user());
        return $query->count();
    }

    public function totalBySupplier($supplierId = null)
    {
        $query = $this->model
            ->where('type', 2)
            ->where('supplier_id', $supplierId)
            ->where(function ($query) {
                $query->whereIn('status', [Order::SUCCESS, Order::RETURN]);
            });

        build_query_by_user_id($query, auth()->user());

        $result = $query->get();

        $cost = collect($result)->sum('base_cost');


        return $cost;
    }

}
