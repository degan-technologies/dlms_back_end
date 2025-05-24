<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EbookReading extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'ebook_id', 'read_count'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ebook()
    {
        return $this->belongsTo(EBook::class);
    }
}
