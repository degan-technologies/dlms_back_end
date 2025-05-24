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
        'cover_image',
        'language_id',
        'category_id',
        'library_id',
        'subject_id',
        'grade_id',
        'user_id',
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



    public function language() {
        return $this->belongsTo(Language::class);
    }

    public function subject() {
        return $this->belongsTo(Subject::class);
    }

    public function grade() {
        return $this->belongsTo(Grade::class);
    }

    public function user(){
       return $this->belongsTo(User::class);
    }
}
