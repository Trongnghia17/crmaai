<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class UserPackage extends Model
{
    protected $table = 'user_package';
    protected $fillable = [
        'name',
        'user_id',
        'package_id',
        'is_active',
        'days',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


