<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'contact_person',
        'contact_person_phone',
        'status',
        'user_id',
        'total_money',
    ];
    protected $casts = [
        'status' => 'boolean',
    ];
}
