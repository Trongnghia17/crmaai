<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',

        'check_in_time',
        'check_in_lat',
        'check_in_lng',
        'check_in_image',
        'check_in_distance',

        'check_out_time',
        'check_out_lat',
        'check_out_lng',
        'check_out_image',
        'check_out_distance',

        'status'
    ];

    protected $casts = [
        'work_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime'
    ];

    // 🔗 thuộc user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔥 helper cực tiện
    public function isCheckedIn()
    {
        return !is_null($this->check_in_time);
    }

    public function isCheckedOut()
    {
        return !is_null($this->check_out_time);
    }
}
