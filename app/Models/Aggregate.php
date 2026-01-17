<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aggregate extends Model
{
    use HasFactory;

    protected $table = 'aggregate';
    protected $primaryKey = 'id';
    protected $fillable = [
        'date',
        'user_id',
        'time',
        'total'
    ];
}
