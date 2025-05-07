<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'StaffID';
    protected $fillable = [
        'user_id','FirstName','LastName','library_branch_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function branch()
    {
        return $this->belongsTo(LibraryBranch::class,'library_branch_id');
    }
}

