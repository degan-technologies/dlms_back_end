<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'type',
        'notifiable_id',
        'notifiable_type',
        'read_at',
    ];

    protected $dates = [
        'read_at',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
