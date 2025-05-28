<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bookmark extends Model
{
    use SoftDeletes;    protected $fillable = [
        'user_id',
        'e_book_id',
        'title',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }    public function ebook()
    {
        return $this->belongsTo(EBook::class, 'e_book_id');
    }
}
