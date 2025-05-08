<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Students extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id','FirstName','LastName','Address',
        'grade','section','sex','BranchID','gender'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function branch()
    {
        return $this->belongsTo(LibraryBranch::class,);
    }
}

