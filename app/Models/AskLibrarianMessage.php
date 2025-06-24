<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AskLibrarianMessage extends Model
{
    protected $fillable = [
        'session_id',
        'parent_id',
        'name',
        'email',
        'sender',
        'message',
        'file_url',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AskLibrarianMessage::class, 'parent_id');
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(AskLibrarianMessage::class, 'id', 'parent_id');
    }
}
