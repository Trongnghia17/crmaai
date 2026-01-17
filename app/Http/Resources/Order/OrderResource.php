<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\OrderDetail\OrderDetailResource;
use App\Models\Order;
use App\Repositories\Order\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $retailCost = ($this->status == 4) ? $this->retail_cost * -1 : $this->retail_cost;
        $baseCost = ($this->status == 4) ? $this->base_cost * -1 : $this->base_cost;
        $totalPayemt = 0;
        foreach ($this->orderPayment as $payment)
        {
            $PriceCurrent = $retailCost - $totalPayemt;
            $amountPrice = min($payment->price, $PriceCurrent);
            if ($amountPrice < 0) {
                break;
            }
            $totalPayemt += $amountPrice;
        }
        if ($this->type == 1 || $this->type == 3) {
            $debt = round($this->retail_cost - $totalPayemt);
        } else {
            $debt = round($this->base_cost - $totalPayemt);
        }
//        if ($this->vat > 0) {
//            $serviceFee = ($this->service_fee * 100) / (100 + $this->vat);
//        }

        $customer = $this->customer;
        if ($customer) {
            $customer_debt = (new OrderRepository())->debtByPhone($customer->id) + ($customer->debt_old - $customer->pay_debt_old);
        }

        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'phone' => $this->customer?->phone,
            'email' => $this->customer?->email,
            'name' => $this->customer?->name,
            'customer_debt' => $customer_debt ?? null,
            'code' => $this->code,
            'address' => $this->customer?->address,
            'shipping_code' => $this->shipping_code,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'base_cost' => round($baseCost),
            'wholesale_cost' => ($this->status == 4) ? round($this->wholesale_cost) * -1 : round($this->wholesale_cost),
            'retail_cost' => round($retailCost),
            'type' => $this->type,
            'debt' => ($this->status == 4) ? 0 : $debt,
            'payment_type' => $this->payment_type,
            'vat' => $this->vat,
            'user' => $this->user,
//            'order_refund_show' => OrderRefundShowResource::collection(collect($this->orderRefund)->groupBy('order_id')),
//            'order_refund' => OrderRefundResource::collection($this->orderRefund),
            'status' => $this->status,
            'is_refund' => $this->is_refund,
            'order_detail' => OrderDetailResource::collection($this->orderDetail),
            'supplier' => $this->supplier,
            'order' => $this->status_order == Order::RETURN ?
                Order::query()->select('id', 'code')->find($this->order_id)
                : null,
            'order_payment' => $this->orderPayment,
            'is_retail' => $this->is_retail,
//            'number_customer' => $this->number_customer,
//            'image_attach' => json_decode($this->image_attach),
            'create_date' => $this->create_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
