<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'ReservationID';
    protected $fillable = [
        'MemberID','BookItemID',
        'ReservationDate','library_branch_id','Status'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class,'MemberID','MemberID');
    }
    public function bookItem()
    {
        return $this->belongsTo(BookItem::class,'BookItemID','BookItemID');
    }
    public function branch()
    {
        return $this->belongsTo(LibraryBranch::class,'library_branch_id');
    }
}

