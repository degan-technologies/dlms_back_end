<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'isbn';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'isbn',
        'title',
        'author',
        'publication_year',
        'edition',
        'category_id',
        'publisher_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function bookItems()
    {
        return $this->hasMany(BookItem::class, 'isbn', 'isbn');
    }
}
