<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookCondition extends Model
{
    use SoftDeletes;
    protected $fillable = ['book_item_id','condition','note'];

    public function bookItem()
    {
        return $this->belongsTo(BookItem::class);
    }
}

