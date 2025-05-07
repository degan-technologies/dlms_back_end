<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Library extends Model
{
    
    use SoftDeletes;
    protected $fillable = ['branch_id','name'];

    public function branch()
    {
        return $this->belongsTo(LibraryBranch::class, 'branch_id');
    }
}

