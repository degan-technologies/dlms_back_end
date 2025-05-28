<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryBranch extends Model {

    use SoftDeletes;

    protected $fillable = [
        'branch_name',
        'address',
        'contact_number',
        'email',
        'location',
        'library_time',
    ];

    protected $casts = [
        'library_time' => 'array',
    ];

    public function library() {
        return $this->hasMany(Library::class);
    }
}
