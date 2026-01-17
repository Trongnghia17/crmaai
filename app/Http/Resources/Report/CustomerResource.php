<?php

namespace App\Http\Resources\Report;

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
            'total_pay' => $this['total_pay'],
            'number' => $this['number'],
            'total' => $this['total'],
            'customer' => $this['customer'],
        ];
    }
}
