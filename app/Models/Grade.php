<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model {
    use SoftDeletes;
    protected $fillable = [
        'name',

    ];

    public function bookItems() {
        return $this->hasMany(BookItem::class);
    }


    public function students() {
        return $this->hasMany(User::class);
    }

    public function sections() {
        return $this->hasMany(Section::class);
    }
}
