<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Category extends Model
{
    protected $table = 'category';
    protected $fillable = [
        'name',
        'user_id',
        'is_active',

    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsToMany(Product::class, 'category_product', 'category_id', 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


