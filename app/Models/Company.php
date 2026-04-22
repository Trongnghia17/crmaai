<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'lat',
        'lng',
        'radius'
    ];

    // 🔗 1 công ty có nhiều user
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
