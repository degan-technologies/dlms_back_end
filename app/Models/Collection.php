<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model {

    protected $fillable = [
        'name',
        'e_book_id',
        'user_id',
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function ebooks() {
        return $this->belongsToMany(EBook::class, 'collection_ebook', 'collection_id', 'e_book_id');
    }
}
