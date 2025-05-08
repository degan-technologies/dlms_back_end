<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'isbn',
        'item_type',  // 'physical', 'ebook', 'other'
        'availability_status', // 'available', 'checked_out', 'reserved', 'lost', 'damaged'
        'author',
        'publication_year',
        'description',
        'cover_image_url',
        'metadata',
        'language',
        'library_branch_id',
        'shelf_id',
        'category_id',
        'publisher_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    // Item type constants for better code maintenance
    const TYPE_PHYSICAL = 'physical';
    const TYPE_EBOOK = 'ebook';
    const TYPE_OTHER = 'other';

    // Availability status constants
    const STATUS_AVAILABLE = 'available';
    const STATUS_CHECKED_OUT = 'checked_out';
    const STATUS_RESERVED = 'reserved';
    const STATUS_LOST = 'lost';
    const STATUS_DAMAGED = 'damaged';

    /**
     * Get the book associated with this book item.
     * This is for physical books.
     */
    public function book(): HasOne
    {
        return $this->hasOne(Book::class);
    }

    /**
     * Get the ebook associated with this book item.
     * This is for electronic books.
     */
    public function ebook(): HasOne
    {
        return $this->hasOne(EBook::class);
    }

    /**
     * Get the other asset associated with this book item.
     * This is for non-book library assets (DVDs, equipment, etc).
     */
    public function otherAsset(): HasOne
    {
        return $this->hasOne(OtherAsset::class);
    }

    /**
     * Get the library branch that owns this book item.
     */
    public function libraryBranch(): BelongsTo
    {
        return $this->belongsTo(LibraryBranch::class);
    }

    /**
     * Get the shelf that owns this book item.
     */
    public function shelf(): BelongsTo
    {
        return $this->belongsTo(Shelf::class);
    }

    /**
     * Get the category that owns this book item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the publisher associated with this book item.
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    /**
     * Get the loans for this book item.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get the condition records for this book item.
     */
    public function conditions()
    {
        return $this->hasMany(BookCondition::class);
    }

    /**
     * Get the current condition of the book item.
     */
    public function currentCondition()
    {
        return $this->conditions()->latest()->first();
    }
    
    /**
     * Get the reservations for this book item.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    
    /**
     * Check if the item is available for reservation or checkout
     */
    public function isAvailable()
    {
        return $this->availability_status === self::STATUS_AVAILABLE;
    }
    
    /**
     * Get the specific details based on item type
     */
    public function getSpecificItemDetails()
    {
        if ($this->item_type === self::TYPE_PHYSICAL) {
            return $this->book;
        } elseif ($this->item_type === self::TYPE_EBOOK) {
            return $this->ebook;
        } else {
            return $this->otherAsset;
        }
    }
}

