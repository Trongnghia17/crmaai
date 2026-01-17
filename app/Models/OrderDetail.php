<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_detail';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'base_cost',
        'wholesale_cost',
        'retail_cost',
        'entry_cost',
        'vat',
        'vat_cost',
        'user_cost',
        'unit',
        'discount',
        'discount_type',
        'base_cost_base',
        'retail_cost_base',

    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

