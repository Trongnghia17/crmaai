<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Inventory\InventoryDetailResource;
use App\Http\Resources\OrderDetail\OrderDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
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
            'status' => $this->status,
            'user' => $this->user,
            'inventories_detail' => InventoryDetailResource::collection($this->inventoryDetail),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'note' => $this->note
        ];
    }
}
