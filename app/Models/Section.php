<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model {
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'grade_id',
    ];

    public function grade() {
        return $this->belongsTo(Grade::class);
    }

    public function students() {
        return $this->hasMany(User::class);
    }



    public function bookItems() {
        return $this->hasMany(BookItem::class);
    }
}
