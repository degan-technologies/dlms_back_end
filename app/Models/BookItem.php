<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookItem extends Model {
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'author',
        'description',
        'cover_image_url',
        'language_id',
        'category_id',
        'grade_id',
        'library_id',
        'shelf_id',
        'subject_id',
    ];

    public function books() {
        return $this->hasMany(Book::class);
    }


    public function ebooks() {
        return $this->hasMany(EBook::class);
    }


    public function library(): BelongsTo {
        return $this->belongsTo(library::class);
    }


    /**
     * Get the category that owns this book item.
     */
    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }


    public function grade(): BelongsTo {
        return $this->belongsTo(Grade::class);
    }

    public function language() {
        return $this->belongsTo(Language::class);
    }

    public function subjects() {
        return $this->belongsTo(Subject::class);
    }
}
