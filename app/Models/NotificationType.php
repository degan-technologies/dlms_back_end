<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationType extends Model
{
    use SoftDeletes;
    protected $fillable = ['type'];

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}

