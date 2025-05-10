<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecentlyViewed extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'book_item_id',
        'last_viewed_at',
        'view_count',
        'last_page_viewed',
        'view_duration',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_viewed_at' => 'datetime',
        'view_count' => 'integer',
        'last_page_viewed' => 'integer',
        'view_duration' => 'integer', // in seconds
        'metadata' => 'array',
    ];

    /**
     * Get the user that viewed the resource.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book item that was viewed.
     */
    public function bookItem(): BelongsTo
    {
        return $this->belongsTo(BookItem::class);
    }
}
