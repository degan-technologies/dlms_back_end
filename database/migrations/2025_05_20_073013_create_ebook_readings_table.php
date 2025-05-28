<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEbookReadingsTable extends Migration
{
    public function up()
    {
        Schema::create('ebook_readings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ebook_id');
            $table->unsignedInteger('read_count')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'ebook_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ebook_id')->references('id')->on('e_books')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ebook_readings');
    }
}
