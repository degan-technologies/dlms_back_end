<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shelf extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'ShelfID';
    protected $fillable = ['shelf_code','section_id','library_branch_id'];

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
    public function branch()
    {
        return $this->belongsTo(LibraryBranch::class, 'library_branch_id');
    }
}
