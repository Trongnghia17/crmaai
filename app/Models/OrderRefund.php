<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
    protected $table = 'order_refund';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_id',
        'order_refund_id',
        'product_id',
        'quantity',
        'user_id',
        'detail_id'
    ];
    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }
}
