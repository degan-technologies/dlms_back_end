<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration {
    public function up() {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('edition')->nullable();
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('title')->nullable();
            $table->string('cover_image')->nullable();
            $table->integer('pages')->nullable();
            $table->boolean('is_borrowable')->default(true); 
            $table->boolean('is_reserved')->default(false);
            $table->year('publication_year')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint
            $table->foreignId('book_item_id')
                  ->references('id')
                  ->on('book_items')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreignId('shelf_id')
                  ->constrained('shelves')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('library_id')
                  ->constrained('libraries')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
        });
    }

    public function down() {
        Schema::dropIfExists('books');
    }
}
