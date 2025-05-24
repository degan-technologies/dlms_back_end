<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model {
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'adress',
        'section_id',
        'gender',
        'grade_id',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function grade() {
        return $this->belongsTo(Grade::class);
    }
    public function section() {
        return $this->belongsTo(Section::class);
    }
}
