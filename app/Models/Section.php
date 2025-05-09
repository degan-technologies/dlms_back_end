<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['section_name','library_branch_id'];

    public function branch()
    {
        return $this->belongsTo(LibraryBranch::class);
    }
    public function shelves()
    {
        return $this->hasMany(Shelf::class);
    }
}
