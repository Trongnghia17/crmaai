<?php

namespace App\Models;

use App\Traits\GetTableName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitLoss extends Model
{
    use HasFactory;
    protected $table = 'profit_loss';
    protected $primaryKey = 'id';
    protected $fillable = [
        "revenue_sale",
        "discount_sale",
        "order_cancel",
        "cost_sale",
        "fee",
        "other_income",
        "other_cost",
        "user_id",
        "time",
        "vat",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
