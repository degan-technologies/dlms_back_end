<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
          
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('returned_date')->nullable();
           
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('book_id')->constrained('books')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('library_id')->constrained('libraries')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('loans');
    }
}
