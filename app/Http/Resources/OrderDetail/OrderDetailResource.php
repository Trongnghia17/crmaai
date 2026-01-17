<?php

namespace App\Http\Resources\OrderDetail;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
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
            'quantity' => $this->quantity,
            'base_cost' => $this->base_cost,
            'wholesale_cost' => $this->wholesale_cost,
            'retail_cost' => $this->retail_cost,
            'user_cost' => ($this->quantity > 0) ? $this->user_cost / $this->quantity : $this->user_cost,
            'base_cost_base' => ($this->quantity > 0) ? $this->base_cost_base / $this->quantity : $this->base_cost_base,
            'retail_cost_base' => ($this->quantity > 0) ? $this->retail_cost_base / $this->quantity : $this->retail_cost_base,
            'discount_type' => $this->discount_type,
            'product' => new ProductResource($this->product),
            'discount' => $this->discount,
            'order_detail_note' => $this->orderDetailNote,
            'sub_unit_quantity' => $this->sub_unit_quantity,
            'size' => $this->size,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
