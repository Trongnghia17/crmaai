<?php

namespace App\Http\Resources\Customer;

use App\Repositories\Order\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            "total_money" => $this->total_money,
            "note" => $this->note,
            "health_needs" => $this->health_needs,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "user_id" => $this->user_id,
            "debt" => (new OrderRepository())->debtByPhone($this->id)
        ];
    }
}
