<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model {
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'book_id',
        'borrow_date',
        'due_date',
        'returned_date',
        'library_id',
    ];



    public function book() {
        return $this->belongsTo(Book::class);
    }

    public function library() {
        return $this->belongsTo(Library::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function fine()
    {
        return $this->hasOne(Fine::class, 'loan_id');
    }
}
