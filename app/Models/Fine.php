<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fine extends Model {
    use SoftDeletes;
    
    protected $fillable = [
        'library_id',
        'user_id',
        'loan_id',
        'fine_amount',
        'receipt_path',
        'fine_date',
        'reason',
        'payment_date',
        'payment_status',
    ];


 protected $casts = [
        'fine_amount' => 'float',
        'fine_date' => 'datetime',
        'payment_date' => 'datetime',
        'payment_status' => 'boolean',
    ];
    public function library() {
        return $this->belongsTo(Library::class);
    }
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function loan() {
        return $this->belongsTo(Loan::class, 'LoanID');
    }
}
