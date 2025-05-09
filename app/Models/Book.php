<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The primary key for this model.
     * We're using book_item_id as the primary key.
     */
    protected $primaryKey = 'book_item_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_item_id',
        'edition',
        'pages',
        'cover_type',
        'dimensions',
        'weight_grams',
        'barcode',
        'shelf_location_detail',
        'reference_only',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'reference_only' => 'boolean',
        'weight_grams' => 'integer',
        'pages' => 'integer',
    ];

    /**
     * Get the book item that owns the book.
     */
    public function bookItem(): BelongsTo
    {
        return $this->belongsTo(BookItem::class, 'book_item_id');
    }

    /**
     * Get the publisher associated with the book through book item.
     */
    public function publisher()
    {
        return $this->bookItem->publisher();
    }

    /**
     * Get the category associated with the book through book item.
     */
    public function category()
    {
        return $this->bookItem->category();
    }

    /**
     * Get all loans for this book.
     */
    public function loans()
    {
        return $this->bookItem->loans();
    }
    
    /**
     * Get the title of the book from its parent BookItem.
     */
    public function getTitle()
    {
        return $this->bookItem->title;
    }
    
    /**
     * Get the author of the book from its parent BookItem.
     */
    public function getAuthor()
    {
        return $this->bookItem->author;
    }
    
    /**
     * Get the ISBN of the book from its parent BookItem.
     */
    public function getIsbn()
    {
        return $this->bookItem->isbn;
    }
}
