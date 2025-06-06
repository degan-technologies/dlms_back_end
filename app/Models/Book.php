<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model {
    use SoftDeletes;

    protected $fillable = [
        'edition',
        'isbn',
        'title',
        'pages',
        'is_borrowable',
        'cover_image',
        'book_item_id',
        'shelf_id',
        'library_id',
        'is_reserved',
        'publication_year',
    ];


    protected $casts = [
        'is_borrowable' => 'boolean',
        'pages' => 'integer',
        'is_reserved' => 'boolean',
    ];


    public function bookItem(): BelongsTo {
        return $this->belongsTo(BookItem::class);
    }


    public function shelf(): BelongsTo {
        return $this->belongsTo(Shelf::class, 'shelf_id');
    }

    public function library(): BelongsTo {
        return $this->belongsTo(Library::class);
    }

    public function bookCondition() {
        return $this->hasOne(BookCondition::class);
    }

    public function reservations() {
        return $this->hasMany(Reservation::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
