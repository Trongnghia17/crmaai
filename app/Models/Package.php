<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Package extends Model
{
    protected $table = 'packages';
    protected $guarded = ['id'];

    protected $primaryKey = 'id';

    protected $casts = [
        'is_active' => 'boolean',
    ];


    public static function trialPackage(){
        return static::where('code', 'TRIAL')
            ->where('is_active', true)
            ->first();
    }
    public function user_package()
    {
        return $this->belongsTo(User::class);
    }
}


