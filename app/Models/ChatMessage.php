<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */    protected $fillable = [
        'e_book_id',
        'user_id',
        'question',
        'highlight_text',
        'ai_response',
        'page_number',
        'sent_at',
        'is_anonymous'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_anonymous' => 'boolean',
        'page_number' => 'integer',
        'sent_at' => 'datetime',
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }    public function ebook()
    {
        return $this->belongsTo(EBook::class, 'e_book_id');
    }
}
