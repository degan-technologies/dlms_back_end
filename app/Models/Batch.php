<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'grade',
        'str_section',
        'int_section',
        'year',
    ];

    protected $casts = [
        'year' => 'date',
        'is_current' => 'boolean'
    ];
}
