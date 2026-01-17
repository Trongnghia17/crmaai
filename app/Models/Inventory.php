<?php

namespace App\Models;

use App\Traits\GetTableName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $table = 'inventory';
    protected $fillable = [
        'user_id',
        'code',
        'type',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function inventoryDetail()
    {
        return $this->hasMany(InventoryDetail::class);
    }
}
