<?php

namespace App\Http\Resources\ReceiptPayment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'time' => $this->time,
            'partner_group_id' => $this->partner_group_id,
            'partner_group_name' => $this->partner_group_name,
            'partner_name' => $this->partner_name,
            'partner_id' => $this->partner_id,
            'price' => ($this->type == 1) ? $this->price : $this->price * -1,
            'payment_type' => $this->payment_type,
            'is_other_income' => $this->is_other_income,
            'note' => $this->note,
            'receipt_type' => $this->receipt_type_id ? $this->receiptType : null,
            'type' => $this->type,
            'order' => $this->order_id ? $this->order : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
