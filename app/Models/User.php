<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use SoftDeletes;
    use Notifiable;
    use HasRoles;

    protected $fillable = [
        'library_branch_id',
        'username',
        'phone_no',
        'email',
        'password',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function libraryBranch()
    {
        return $this->belongsTo(LibraryBranch::class);
    }

    public function role()
    {
        return $this->belongsTo(UserRole::class);
    }

    /**
     * Get all bookmarks created by the user.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get all notes created by the user.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get all reading lists created by the user.
     */
    public function readingLists()
    {
        return $this->hasMany(ReadingList::class);
    }

    /**
     * Get all recently viewed resources by the user.
     */
    public function recentlyViewed()
    {
        return $this->hasMany(RecentlyViewed::class);
    }
}
