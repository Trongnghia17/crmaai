<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptPayment extends Model
{
    public const STATUS_SUCCESS = 1;
    public const STATUS_CANCEL = 1; // Changed to 0 as it shouldn't have the same value as SUCCESS
    public const BANK = 'bank';
    public const CASH = 'cash';
    public const COD = 'cod';
    public const CREDITS = 'credits';
    public const SUPPLIER = 'Nhà cung cấp';
    public const CUSTOMER = 'Khách hàng';
    public const EMPLOYEE = 'Nhân viên';
    public const PARTNER_SHIP = 'Đối tác vận chuyển';
    public const PARTNER_DIF = 'Đối tượng khác';
    protected $table = 'receipt_payment';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $casts = [
        'is_edit' => 'boolean',
        'status' => 'boolean',
        'is_other_income' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function receiptType()
    {
        return $this->belongsTo(ReceiptType::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'partner_id', 'id');
    }
}
