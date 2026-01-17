<?php

namespace App\Http\Resources\Customer;

use App\Repositories\Order\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerCompactResource extends JsonResource
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
            "name" => $this->name,
            "phone" => $this->phone,
            "email" => $this->email,
            "address" => $this->address,
            "status" => $this->status,
            "total_order" => (new OrderRepository())->totalOrderByPhone($this->id),
            "total_pay" => (new OrderRepository())->totalByPhone($this->id),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "debt" => (new OrderRepository())->debtByPhone($this->id),
            "manager" => $this->user ?? null,
        ];
    }
}
