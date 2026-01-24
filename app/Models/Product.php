<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    // use Searchable; // Tạm tắt để không cần Meilisearch

    protected $fillable = [
        'user_id',
        'name',
        'image',
        'description',
        'is_active',
        'is_buy_always',
        'sku',
        'base_cost',
        'retail_cost',
        'wholesale_cost',
        'discount',
        'discount_type',
        'in_stock',
        'sold',
        'temporality',
        'available',
        'unit',
        'barcode',
        'is_show'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_buy_always' => 'boolean',
        'is_show' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id, // PHẢI có dòng này
            'barcode' => $this->barcode,
            'sku' => $this->sku,
        ];
    }
}
