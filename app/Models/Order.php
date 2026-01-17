<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const PENDING = 1;
    const CANCELLED = 3;
    const RETURN = 4;
    const SUCCESS = 2;
    const CANCEL_SUCCESS = 5;

    const SALES_ORDER = 1;
    const PURCHASE_ORDER = 2;

    protected $table = 'orders';

    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'phone',
        'customer_name',
        'customer_id',
        'email',
        'base_cost',
        'retail_cost',
        'wholesale_cost',
        'entry_cost',
        'base_cost_base',
        'retail_cost_base',
        'vat',
        'code',
        'order_id',
        'discount',
        'created_date',
        'type',
        'status',
        'payment_status',
        'payment_type',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_retail' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }
    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }

    public function orderPayment()
    {
       return $this->hasMany(OrderPayment::class);
    }
    public function orderRefund()
    {
        return $this->hasMany(OrderRefund::class, 'order_refund_id', 'id');
    }
    public function getPartnerInfo()
    {
        if (in_array($this->type, [1, 3])) {
            return [
                'id' => $this->customer_id,
                'name' => $this->customer_id ? $this->name : 'Khách lẻ',
                'phone' => $this->customer_id ? $this->phone : null,
                'address' => $this->customer_id ? $this->address : null,
            ];
        }

        $supplier = $this->supplier;
        return [
            'id' => $supplier->id ?? null,
            'name' => $supplier->name ?? 'Đại lý',
            'phone' => $supplier->phone ?? null,
            'address' => $supplier->address ?? null,
        ];
    }
}
