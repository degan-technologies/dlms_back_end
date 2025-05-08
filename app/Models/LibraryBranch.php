<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryBranch extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'library_id',
        'branch_name',
        'address',
        'contact_number',
        'email',
        'opening_hours',
    ];

    

    public function library()
    {
        return $this->hasMany(Library::class);
    }
}
