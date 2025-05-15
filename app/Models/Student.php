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
        'address',
        // 'grade_id',
        // 'section_id',
        'sex',
        'library_branch_id',
        'gender'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function libraryBranch() {
        return $this->belongsTo(LibraryBranch::class);
    }
    public function grade() {
        return $this->belongsTo(Grade::class);
    }
    public function section() {
        return $this->belongsTo(Section::class);
    }

    public function reservations() {
        return $this->hasMany(Reservation::class);
    }
}
