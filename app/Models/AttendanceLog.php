<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
   protected $fillable = [
        'user_id',
        'type',
        'lat',
        'lng',
        'image',
        'distance',
        'device',
        'ip'
    ];

    // 🔗 thuộc user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
