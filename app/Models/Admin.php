<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'FirstName',
        'LastName',
        'email',
        'phone_no',
        'user_id',
        'library_branch_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function libraryBranch()
    {
        return $this->belongsTo(LibraryBranch::class, 'library_branch_id');
    }
}
