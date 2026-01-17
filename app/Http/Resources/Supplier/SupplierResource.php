<?php

namespace App\Http\Resources\Supplier;

use App\Repositories\Order\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'contact_person' => $this->contact_person,
            'contact_person_phone' => $this->contact_person_phone,
            'surrogate' => $this->surrogate,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            "user_id" => $this->user_id,
            "debt" => (new OrderRepository())->debtBySupplier($this->id),
            "total_order" => (new OrderRepository())->totalOrderBySupplier($this->id),
            "total_pay" => (new OrderRepository())->totalBySupplier($this->id),

        ];
    }
}
