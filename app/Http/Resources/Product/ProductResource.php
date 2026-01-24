<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
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
            'name' => $this->name ,
            'image' => $this->image ? url(Storage::url($this->image)) : null,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'is_buy_always' => $this->is_buy_always,
            'sku' => $this->sku,
            'base_cost' => $this->base_cost,
            'retail_cost' => $this->retail_cost,
            'wholesale_cost' => $this->wholesale_cost,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'in_stock' => $this->in_stock,
            'sold' => $this->sold,
            'unit' => $this->unit,
            'barcode' => $this->barcode,
            'temporality' => $this->temporality,
            'available' => $this->available,
            'category_ids' => $this->category->pluck('id')->toArray(),
            'is_show' => $this->is_show,
            'created_by' => $this->user,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
