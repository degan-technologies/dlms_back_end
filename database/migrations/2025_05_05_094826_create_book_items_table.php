<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('book_items', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('author', 255)->nullable();
            $table->text('description')->nullable(); 
            $table->string('cover_image_url')->nullable(); 
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreignId('library_id')
                  ->constrained('libraries')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('shelf_id')
                  ->constrained('shelves')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->foreignId('language_id')
                  ->constrained('languages')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('grade_id')
                  ->nullable() 
                  ->constrained('grades')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->foreignId('subject_id')
                  ->nullable() 
                  ->constrained('subjects')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_items');
    }
}

