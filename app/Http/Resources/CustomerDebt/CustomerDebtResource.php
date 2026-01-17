<?php

namespace App\Http\Resources\CustomerDebt;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDebtResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "customer_name" => $this->customer?->name,
            "customer_phone" => $this->customer?->phone,
            "price" => $this->price,
            "previous_debt" => $this->previous_debt,
            "remaining_debt" => $this->remaining_debt,
            "note" => $this->note,
            "type" => $this->type,
            "created_at" => $this->created_at,
            "user" => $this->user,
        ];
    }
}
