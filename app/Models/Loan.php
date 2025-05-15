<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'student_id',
        'book_item_id',
        'borrow_date',
        'due_date',
        'return_date',
        'library_branch_id',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function bookItem()
    {
        return $this->belongsTo(BookItem::class);
    }

    public function libraryBranch()
    {
        return $this->belongsTo(LibraryBranch::class);
    }
    
    public function fine()
    {
        return $this->hasOne(Fine::class, 'loan_id');
    }
}
