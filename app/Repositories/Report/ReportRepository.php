<?php

namespace App\Repositories\Report;

use App\Models\OrderDetail;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Log;

class ReportRepository extends BaseRepository implements ReportRepositoryInterface
{
    /**
     * Create a new class instance.
     *
     */

    protected $model;

    public function aggregate($orders)
    {
        $data = [];
        $orders = $orders->groupBy('product_id');
        foreach ($orders ?? [] as $key => $item) {
            $quantitySuccess = $item->where('order.status', 2)->sum('quantity');
            $quantityRefund = $item->where('order.status', 4)->sum('quantity');
            $subUnitQuantitySuccess = $item->where('order.status', 2)->sum('sub_unit_quantity');
            $subUnitQuantityRefund = $item->where('order.status', 4)->sum('sub_unit_quantity');

            foreach ($item ?? [] as $k => $value) {
                $item[$k]['retail_cost_before_discount'] = $this->calculateRetailCost($value);
                $item[$k]['base_cost_before_discount'] = $this->calculateBaseCost($value);
                $item[$k]['retail_cost_before_vat'] = $this->calculateCostVat($value, $item[$k]['retail_cost_before_discount']);
                $item[$k]['base_cost_before_vat'] = $this->calculateCostVat($value, $item[$k]['base_cost_before_discount']);
            }

            $data[] = [
                'base_cost' => $item->sum('base_cost'),
                'wholesale_cost' => $item->sum('wholesale_cost'),
                'retail_cost' => $item->sum('retail_cost'),
                'retail_cost_before_vat' => $item->sum('retail_cost_before_vat'),
                'base_cost_before_vat' => $item->sum('base_cost_before_vat'),
                'retail_cost_before_discount' => $item->sum('retail_cost_before_discount'),
                'base_cost_before_discount' => $item->sum('base_cost_before_discount'),
                'entry_cost' => $item->sum('entry_cost'),
                'vat_cost' => $item->sum('vat_cost'),
                'quantity' => $quantitySuccess - $quantityRefund,
                'sub_unit_quantity' => $subUnitQuantitySuccess - $subUnitQuantityRefund,
                'product' => $item[0]->product,
            ];
            Log::info('baser code' . json_encode($data));
        }


        return $data;

    }

    static public function calculateCostVat($item, $cost)
    {
        $vat = $item->order->vat ?? 0;
        return $cost * (100 + $vat) / 100;
    }

    static public function calculateRetailCost($item)
    {
        $discount = $item->order->discount ?? 0;
        $discountType = $item->order->discount_type ?? 1;
        if ($discountType == 1)  {
            return $item['retail_cost'] * (100 - $discount) / 100;
        } else {
            if ($discount > 0) {
                $orderId = $item->order->id;
                if ($item->order->is_refund) {
                    $orderId = $item->order->order_id;
                }
                $sumRetailCost = OrderDetail::query()->where('order_id', $orderId)->sum('retail_cost');

                $percentOfProduct = 1;
                if ($sumRetailCost > 0) {
                    $percentOfProduct = $item['retail_cost'] / $sumRetailCost;
                }

                $discountOfProduct = $discount * $percentOfProduct;

                return $item['retail_cost'] - $discountOfProduct;
            }

            return $item['retail_cost'];
        }
    }

    static public function calculateBaseCost($item)
    {
        $discount = $item->order->discount ?? 0;
        if ($item->order->is_refund) {
            $discount = $item->order->discount_refund ?? 0;

        }
        Log::info('discount' . $discount);
        $discountType = $item->order->discount_type ?? 1;

        if ($discountType == 1) {
            return $item['base_cost'] * (100 - $discount) / 100;
        } else {
            if ($discount > 0 && $item['base_cost'] > 0) {
                $orderId = $item->order->id;
                $sumRetailCost = OrderDetail::query()->where('order_id', $orderId)->sum('base_cost');

                if ($sumRetailCost > 0) {
                    $percentOfProduct = $item['base_cost'] / $sumRetailCost;
                } else {
                    $percentOfProduct = 1;
                }

                $discountOfProduct = $discount * $percentOfProduct;

                return $item['base_cost'] - $discountOfProduct;
            }

            return $item['base_cost'];
        }
    }

    public function getModel()
    {
        return \App\Models\OrderDetail::class;
    }
}
