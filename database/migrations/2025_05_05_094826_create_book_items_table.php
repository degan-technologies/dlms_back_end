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
            $table->string('isbn', 20)->nullable();
            $table->string('item_type', 20); // 'physical', 'ebook', 'other'
            $table->string('availability_status', 20)->default('available'); // 'available', 'checked_out', 'reserved', 'lost', 'damaged'
            $table->string('author', 255)->nullable();
            $table->year('publication_year')->nullable();
            $table->text('description')->nullable(); // Changed from string to text for longer descriptions
            $table->string('cover_image_url')->nullable(); // Added for all types of items
            $table->json('metadata')->nullable(); // Added for flexible additional data
            $table->string('language', 50)->nullable(); // Moved from books table to parent
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreignId('library_branch_id')
                  ->constrained('library_branches')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('shelf_id')
                  ->nullable() // Made nullable since ebooks might not have a physical shelf
                  ->constrained('shelves')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreignId('publisher_id')
                  ->nullable()
                  ->constrained('publishers')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_items');
    }
}

