<?php

namespace App\Repositories\OrderDetail;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Repositories\BaseRepository;
use App\Repositories\Product\ProductRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\OrderRefund;
use Illuminate\Http\Request;

class OrderDetailRepository extends BaseRepository implements OrderDetailRepositoryInterface
{
    protected $model;
    protected $productRepo;
    public function __construct()
    {
        parent::__construct();
        $this->productRepo = new ProductRepository();
    }
    public function getModel()
    {
        return OrderDetail::class;
    }
    /*
    Hàm xử  lý order detail của đơn nhập
    */
    public function getDataPurchase($orderDetails, $order)
    {
        $data = [];

        foreach ($orderDetails ?? [] as $detail) {
            $vat = $detail['vat'] ?? 0;
            $discount = $detail['discount'] ?? 0;

            $productFromDetail = $this->productRepo->find($detail['product_id']);

            $data[] = $this->__getPriceOrderDetail($productFromDetail, $vat, $discount, $detail, $order);

            if ($order->status == Order::RETURN) {
                $productFromDetail->available -= $detail['quantity'];
                $productFromDetail->in_stock -= $detail['quantity'];
                $productFromDetail->temporality -= $detail['quantity'];
                $this->__savingRefundOrderPivot($detail, $order);
            } else if ($order->status == Order::SUCCESS) {
                $productFromDetail->available += $detail['quantity'];
                $productFromDetail->in_stock += $detail['quantity'];
                $productFromDetail->temporality += $detail['quantity'];
            }
            $productFromDetail->base_cost = $detail['price'];//đặt giá nhập = giá của sản phẩm trong đơn hàng!
            $productFromDetail->save();

        }
        return $data;

    }
    /*
    Hàm xử  lý order detail của đơn bán
    */
    public function getDataSales($orderDetails, $order)
    {
        $data = [];
        foreach ($orderDetails ?? [] as $detail) {
            $vat = $detail['vat'] ?? 0;
            $discount = $detail['discount'] ?? 0;

            $productFromDetail = $this->productRepo->find($detail['product_id']);

            if (!$productFromDetail->is_buy_always && $detail['quantity'] > $productFromDetail->temporality && !$order->status == Order::RETURN) {
                return ['code' => 2, 'msg' => "Sản phẩm " . $productFromDetail->name . ' Không được bán âm'];
            }

            $data[] = $this->__getPriceOrderDetailSales($productFromDetail, $vat, $discount, $detail, $order);
            if (!$order->status == Order::RETURN) {
                $this->__savingRefundOrderPivot($detail, order: $order);
            } else {
                $productFromDetail->temporality -= $detail['quantity'];
                if ($order->status == Order::SUCCESS) {
                    $productFromDetail->available -= $detail['quantity'];
                    $productFromDetail->sold += $detail['quantity'];
                }
            }

            $productFromDetail->save();
        }
        return $data;
    }
    /*
        The bellow function is to calculate price of each order detail before saving it to DB
    */
    private function __getPriceOrderDetail($product, $vat, $discount, $detail, $order)
    {
        $baseCostVat = $baseCost = $detail['price'] * $detail['quantity'];
        $entryCost = $product->entry_cost * $detail['quantity'];

        if ($vat > 0) {
            $baseCost = $baseCostVat = $baseCost + ($baseCost * $vat / 100);
        }
        // Apply discount if applicable
        if ($discount > 0) {
            if (isset($detail['discount_type']) && $detail['discount_type'] == 2) {
                // Fixed amount discount
                $baseCost = $order->status == Order::RETURN ? $detail['price_refund'] : $baseCost - $discount;
            } else {
                // Percentage discount
                $baseCost = $baseCost - ($baseCost * $discount / 100);
            }
        }

        // Calculate common values
        $retailCost = $product->retail_cost * $detail['quantity'];
        $wholesaleCost = $product->wholesale_cost * $detail['quantity'];
        $baseCostBase = $detail['price'] * $detail['quantity'];
        $userCost = $baseCostBase;

        // Prepare return data with the correct sign based on order status
        $sign = $order->status == Order::RETURN ? '-' : '';

        return array_merge([
            "retail_cost" => $sign . $retailCost,
            "retail_cost_base" => $sign . $retailCost,
            "wholesale_cost" => $sign . $wholesaleCost,
            "entry_cost" => $order->status == Order::RETURN ? $product->entry_cost : $entryCost,
            "base_cost" => $sign . ($order->status == Order::RETURN ? $product : $baseCost),
            "base_cost_base" => $sign . $baseCostBase,
            "vat_cost" => $sign . $baseCostVat,
            "order_id" => $order->id,
            "vat" => $vat,
            "discount" => $discount,
            "user_cost" => $userCost
        ], $detail);
    }
    private function __getPriceOrderDetailSales($product, $vat, $discount, $detail, $order)
    {
        $retailCostVat = $retailCost = $detail['price'] * $detail['quantity'];
        $retail_cost_base = $order->is_retail ? $product->retail_cost : $product->wholesale_cost;

        // If product has no price or configured price is less than sale price,
        // use sale price for profit/loss calculation
        if ($retail_cost_base <= 0 || $retail_cost_base < $detail['price']) {
            $retail_cost_base = $detail['price'];
        }
        // Apply VAT if applicable
        if ($vat > 0) {
            $retailCostVat = $retailCost * (1 + $vat / 100);
            $retailCost = $retailCostVat;
        }

        // Apply discount if applicable
        if ($discount > 0) {
            if (isset($detail['discount_type']) && $detail['discount_type'] == 2) {
                // Fixed amount discount
                $retailCost = $order->is_refund ? $detail['price_refund'] : $retailCost - $discount;
            } else {
                // Percentage discount - simplify calculation
                $retailCost *= (1 - $discount / 100);
            }
        }
        // Determine if it's a return order
        $isReturn = $order->status == Order::RETURN;
        $sign = $isReturn ? '-' : '';

        // Calculate entryCost based on return condition
        $entryCost = $product->entry_cost * $detail['quantity'];
        if ($isReturn) {
            $orderDetail = $this->findByField('order_id', $order->order_id);
            $productByOrder = $orderDetail->where('product_id', $product->id)->first();
            if ($productByOrder) {
                $entryCost = ($productByOrder->entry_cost / $productByOrder->quantity) * $detail['quantity'];
            }
        }

        // Calculate common values
        $retailCostBase = $retail_cost_base * $detail['quantity'];
        $wholesaleCost = ($product->wholesale_cost == 0) ? $detail['price'] : $product->wholesale_cost * $detail['quantity'];
        $baseCost = $product->base_cost * $detail['quantity'];
        $userCost = $detail['price'] * $detail['quantity'];

        // Return merged array with conditional sign prefixes
        return array_merge([
            "retail_cost_base" => $sign . $retailCostBase,
            "retail_cost" => $sign . $retailCost,
            "wholesale_cost" => $sign . $wholesaleCost,
            "base_cost" => $sign . $baseCost,
            "base_cost_base" => $sign . $baseCost,
            "entry_cost" => $entryCost,
            "vat_cost" => $sign . $retailCostVat,
            "order_id" => $order->id,
            "vat" => $vat,
            "discount" => $discount,
            "user_cost" => $userCost,
        ], $detail);
    }
    private function __savingRefundOrderPivot($detail, $order)
    {
        OrderRefund::query()->create([
            'order_id' => $order->id,
            'order_refund_id' => $order->order_id,
            'product_id' => $detail['product_id'],
            'quantity' => $detail['quantity'],
            'user_id' => auth()->id(),
            'detail_id' => $detail['detail_id'] ?? null
        ]);
    }
    public function __savingWithoutTimestamp($orderDetailData, $order)
    {
        foreach ($data ?? [] as $orderDetailData) {
            $detail = new OrderDetail();
            $detail->order_id = $order->id;
            $detail->created_at = $order->created_at;
            $detail->updated_at = $order->updated_at;
            $detail->timestamps = false;
            foreach ($orderDetailData->toArray() as $key => $value) {
                $detail->{$key} = $value;
            }
            $detail->save();
        }
    }
    public function getDataUpdate($orderDetail, $order)
    {
        foreach ($orderDetail ?? [] as $item) {
            $vat = $item['vat'] ?? 0;
            $discount = $item['discount'] ?? 0;
            $product = $this->productRepo->find($item['product_id']);

            $prices = $this->__getPriceOrderDetail($product, $vat, $discount, $item, $order);

            if (isset($item['id'])) {
                if (isset($item['is_delete']) && $item['is_delete']) {
                    $this->model->whereKey($item['id'])->delete();
                } else {
                    //edit order detail then - quantity inventory
                    if ($order->status_order == Order::SUCCESS) {
                        $product->available += $item['quantity'];
                        $product->temporality += $item['quantity'];
                        $product->in_stock += $item['quantity'];
                    }
                    $product->base_cost = $item['price'];

                    $this->update($item['id'], $prices);
                }
            } else {
                $orderDetail = $this->create($prices);
                if ($orderDetail) {
                    $item['id'] = $orderDetail->id;//assign ID for item
                }
                //create order detail then + quantity inventory
                if ($order->status_order == Order::SUCCESS) {
                    $product->available += $item['quantity'];
                    $product->temporality += $item['quantity'];
                    $product->in_stock += $item['quantity'];
                }

                $product->base_cost = $item['price'];
            }
        }
    }
    public function getDataUpdateSales($orderDetail, $order)
    {
        foreach ($orderDetail ?? [] as $item) {
            if (isset($item['note'])) {
                unset($item['note']);
            }
            $vat = $discount = 0;
            if (isset($item['vat'])) {
                $vat = $item['vat'];
            }

            if (isset($item['discount'])) {
                $discount = $item['discount'];
            }
            $productId = $item['product_id'] ?? null;

            if (!$productId) {
                return ['code' => 1, 'msg' => 'Sản phẩm Không được bỏ trống'];
            }

            $product = $this->productRepo->find($productId);
            $prices = $this->__getPriceOrderDetailSales($product, $vat, $discount, $item, $order);

            if (isset($item['id'])) {
                if (isset($item['is_delete']) && $item['is_delete']) {
                    $detail = $this->model->find($item['id']);
                    $quantity = $detail->quantity;
                    $detail->delete();
                    $product->temporality += $quantity;
                } else {
                    //edit order detail then - quantity inventory
                    $orderDetail = $this->find($item['id']);
                    $product->temporality = ($product->temporality + $orderDetail->quantity) - $item['quantity'];
                    $this->update($item['id'], $prices);
                }
            } else {
                $orderDetail = $this->create($prices);
                if ($orderDetail) {
                    $item['id'] = $orderDetail->id;
                }
                $product->temporality -= $item['quantity'];
            }
            $product->save();
        }
    }
    public function cancelDataUpdate($orderDetail, $order)
    {
        foreach ($orderDetail ?? [] as $item) {

            $productId = isset($item['product_id']) ? $item['product_id'] : null;
            if (!$productId) {
                throw new ModelNotFoundException("Sản phẩm Không được bỏ trống");
            }

            $product = $this->productRepo->find($productId);

            if ($order->type != 2) {
                $product->temporality += $item['quantity'];
                $product->save();
            }
        }
    }

    public function aggregate(Request $request)
    {

        $query = $this->model->with(['product', 'order']) // Bỏ variant và product.categories
        ->where('quantity', '>', 0);
        if ($request->get('name')) {
            $query->whereHas('product', function ($query) use ($request) {
                $query->where('name', 'like', "%" . $request->name . "%");
            });
        }
        $query->whereHas('order', function ($query) use ($request) {
            $query->where('active', true);

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('updated_at', [
                    $request->get('start_date') . ' 00:00:00',
                    $request->get('end_date') . ' 23:59:59'
                ]);
            }

            if ($request->get('user_id')) {
                $query->where('user_id', $request->user_id);
            } else {
                build_query_by_user_id($query, auth()->user());
            }

            $query->where(function ($query) {
                $query->where('status', 2)->orWhere('status', 4);
            });

            $type = $request->get('type', 1);
            if ($type == 1) {
                $query->where(function ($query) {
                    $query->where('type', 1)->orWhere('type', 2);
                });
            } else {
                $query->where('type', 2);
            }

        });

        return $query->orderByDesc('id')->get();
    }


}
