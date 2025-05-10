<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Library extends Model
{

    use SoftDeletes;
    protected $fillable = ['library_branch_id','name', 'address', 'contact_number',];

    public function libraryBranch()
    {
        return $this->belongsTo(LibraryBranch::class );
    }
    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function shelves()
    {
        return $this->hasMany(Shelf::class);
    }

    public function bookItems()
    {
        return $this->hasMany(BookItem::class);
    }
}
