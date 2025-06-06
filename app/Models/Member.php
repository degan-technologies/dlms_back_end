<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'MemberID';
    protected $fillable = [
        'user_id','FirstName','LastName','Address',
        'grade','section','sex','BranchID'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function branch()
    {
        return $this->belongsTo(LibraryBranch::class,'BranchID');
    }
}

