<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EBook extends Model {
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'e_books';


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'book_item_id',
        'file_path',
        'file_name',
        'file_size_mb',
        'pages',
        'is_downloadable',
        'e_book_type_id',
        'user_id',
    ];

    protected $casts = [
        'file_size_mb' => 'float',
        'pages' => 'integer',
        'is_downloadable' => 'boolean',

    ];


    public function ebookType() {
        return $this->belongsTo(EbookType::class, 'e_book_type_id');
    }

    public function bookItem(): BelongsTo {
        return $this->belongsTo(BookItem::class, 'book_item_id');
    }


    public function collections() {
        return $this->belongsToMany(Collection::class, 'collection_ebook', 'e_book_id', 'collection_id');
    }

    public function bookmarks() {
        return $this->hasMany(Bookmark::class, 'e_book_id');
    }
    public function notes() {
        return $this->hasMany(Note::class);
    }
    public function chatMessages() {
        return $this->hasMany(ChatMessage::class);
    }
    public function bookmark() {
        // This will be used with ->with(['bookmark' => function($q) use ($userId) { $q->where('user_id', $userId); }])
        return $this->hasOne(Bookmark::class, 'e_book_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
