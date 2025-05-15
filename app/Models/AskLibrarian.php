<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AskLibrarian extends Model
{
    protected $fillable = [
        'user_id',
        'question',
        'response',
        'library_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function library()
    {
        return $this->belongsTo(Library::class);
    }
}
