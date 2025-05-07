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
            $table->string('isbn',20)->nullable();
            $table->string('item_type',20);
            $table->string('availability_status',20);
            $table->timestamps();
            $table->softDeletes();

    

            $table->foreignId('library_branch_id')
                  ->constrained('library_branches')
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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_items');
    }
}

