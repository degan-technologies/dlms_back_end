<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable {
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

    public function grade()
{
    return $this->belongsTo(Grade::class);
}


    public function libraryBranch() {
        return $this->belongsTo(LibraryBranch::class);
    }

    public function role() {
        return $this->belongsTo(UserRole::class);
    }


    public function bookmarks() {
        return $this->hasMany(Bookmark::class);
    }

    public function notes() {
        return $this->hasMany(Note::class);
    }

    public function chatMessages() {
        return $this->hasMany(ChatMessage::class);
    }


    public function collections() {
        return $this->hasMany(Collection::class);
    }
    public function recentlyVieweds() {
        return $this->hasMany(RecentlyViewed::class);
    }

    public function askLibrarians() {
        return $this->hasMany(AskLibrarian::class);
    }
    public function reservations() {
        return $this->hasMany(Reservation::class);
    }
    public function section(){
        return $this->belongsTo(Section::class);
    }

    public function ebookReadings() {
        return $this->hasMany(EbookReading::class);
    }

}
