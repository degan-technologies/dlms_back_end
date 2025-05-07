<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use SoftDeletes;
    use Notifiable;

    protected $fillable = [
        'library_branch_id',
        'username',
        'phone_no',
        'email',
        'password',
        'role_id',
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
}
