<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Customer extends Model
{
    protected $table = 'customers';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'status',
        'user_id',
        'total_money',
        'note',
        'health_needs',
    ];
    protected $casts = [
        'status' => 'boolean',
        'health_needs' => 'array',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}


