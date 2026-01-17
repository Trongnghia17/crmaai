<?php

namespace App\Http\Resources\Report;

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
            'total_nhap' => (double) $this->total_nhap,
            'total_xuat' => (double) $this->total_xuat + ($this->total_can_bang_kho),
            'start' => (double) $this->start,
            'end' => (double) $this->end,
            'product' => $this->product,
        ];
    }
}
