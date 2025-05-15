<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'department'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}

