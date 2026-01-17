<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDebtHistory extends Model
{
    protected $table = 'customer_debt_history';
    protected $fillable = [
        'customer_id',
        'user_id',
        'order_id',
        'price',
        'previous_debt',
        'remaining_debt',
        'note',
        'type',
    ];
    protected $casts = [
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}


