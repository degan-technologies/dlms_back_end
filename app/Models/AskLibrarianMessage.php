<?php

// // app/Models/AskLibrarianMessage.php
// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class AskLibrarianMessage extends Model
// {
//     use HasFactory;

//     protected $fillable = ['student_id', 'sender_id', 'sender_role', 'message'];

//     public function student()
//     {
//         return $this->belongsTo(User::class, 'student_id');
//     }

//     public function sender()
//     {
//         return $this->belongsTo(User::class, 'sender_id');
//     }
// }
// app/Models/AnonymousChatMessage.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AskLibrarianMessage extends Model
{
    protected $fillable = ['session_id', 'name', 'email', 'sender', 'message','file_url',];
}
