<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope for published announcements
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    // Scope for draft announcements
    public function scopeDraft($query)
    {
        return $query->where('is_published', false);
    }

    // Scope for user's announcements
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
}