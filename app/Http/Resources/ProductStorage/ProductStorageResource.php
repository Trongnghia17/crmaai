<?php

namespace App\Http\Resources\ProductStorage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStorageResource extends JsonResource
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
            'type' => $this->type,
            'quantity_change' => $this->quantity_change,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'product' => $this->product,
            'user' => $this->user,
            'order' => $this->order,
            'partner' => $this->order ? $this->order->getPartnerInfo() : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
