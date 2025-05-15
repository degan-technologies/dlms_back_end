<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbookType extends Model
{
    protected $table = 'e_book_types';

    protected $fillable =[
        'name'
    ];

    public function ebooks()
    {
        return $this->hasMany(EBook::class, 'e_book_type_id');
    }
}
