<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryDetailResource extends JsonResource
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
            'quantity_current' => (double) $this->quantity_current,
            'quantity_reality' => (double) $this->quantity_reality,
            'note' => $this->note,
            'imei' => ($this->imei) ? json_decode($this->imei) : null,
            'product' => new ProductResource($this->product),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
