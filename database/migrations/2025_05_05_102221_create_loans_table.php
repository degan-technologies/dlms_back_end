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
            $table->date('return_date')->nullable();
           
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('student_id')->constrained('students')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('book_item_id')->constrained('book_items')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('library_branch_id')->constrained('library_branches')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('loans');
    }
}
