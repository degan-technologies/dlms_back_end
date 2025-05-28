<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Library extends Model
{

    use SoftDeletes;
    protected $fillable = ['library_branch_id','name',  'contact_number',];

    public function libraryBranch()
    {
        return $this->belongsTo(LibraryBranch::class );
    }
    

    public function shelves()
    {
        return $this->hasMany(Shelf::class);
    }

    public function books(){
        return $this->hasMany(Book::class);
    }

    public function askLibrarians(){
        return $this->hasMany(AskLibrarian::class);
    }

    public function reservations(){
        return $this->hasMany(Reservation::class);
    }
}
