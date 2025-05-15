<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */    protected $fillable = [
        'user_id',
        'e_book_id',
        'content',
        'page_number',
        'highlight_text',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'page_number' => 'integer',
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }    public function ebook()
    {
        return $this->belongsTo(EBook::class, 'e_book_id');
    }
}
