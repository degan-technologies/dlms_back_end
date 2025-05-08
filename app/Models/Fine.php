<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fine extends Model
{
    use SoftDeletes;
   
    protected $fillable = [
        'library_branch_id','user_id','loan_id',
        'fine_amount','fine_date','reason',
        'payment_date','Payment_status'
    ];

    public function branch()
    {
        return $this->belongsTo(LibraryBranch::class,'library_branch_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function loan()
    {
        return $this->belongsTo(Loan::class,'loan_id');
    }
}

