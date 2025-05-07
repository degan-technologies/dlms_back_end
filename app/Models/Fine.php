<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fine extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'FineID';
    protected $fillable = [
        'library_branch_id','user_id','LoanID',
        'FineAmount','FineDate','reason',
        'PaymentDate','PaymentStatus'
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
        return $this->belongsTo(Loan::class,'LoanID');
    }
}

