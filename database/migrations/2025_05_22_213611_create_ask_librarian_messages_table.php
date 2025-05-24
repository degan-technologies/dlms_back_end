<?php

// // database/migrations/xxxx_xx_xx_create_ask_librarian_messages_table.php
// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// class CreateAskLibrarianMessagesTable extends Migration
// {
//     public function up()
//     {
//         Schema::create('ask_librarian_messages', function (Blueprint $table) {
//             $table->id();
//             $table->unsignedBigInteger('student_id'); // Conversation owner
//             $table->unsignedBigInteger('sender_id'); // Who sent the message
//             $table->enum('sender_role', ['student', 'librarian']);
//             $table->text('message');
//             $table->timestamps();

//             $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
//             $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
//         });
//     }

//     public function down()
//     {
//         Schema::dropIfExists('ask_librarian_messages');
//     }
// }




// database/migrations/2025_05_22_213611_create_ask_librarian_messages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ask_librarian_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('session_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('sender');
            $table->text('message');
            $table->string('file_url')->nullable();
            $table->timestamps();

            // Optional: add index for faster lookups
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ask_librarian_messages');
    }
};
