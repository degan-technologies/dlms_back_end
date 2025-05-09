<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration {
    public function up() {
        Schema::create('books', function (Blueprint $table) {
            $table->foreignId('book_item_id')->primary();
            $table->string('edition')->nullable();
            $table->integer('pages')->nullable();
            $table->string('cover_type', 50)->nullable(); // hardcover, paperback, etc.
            $table->string('dimensions')->nullable(); // physical dimensions
            $table->integer('weight_grams')->nullable(); // weight in grams
            $table->string('barcode')->nullable(); // physical barcode
            $table->string('shelf_location_detail')->nullable(); // specific location details
            $table->boolean('reference_only')->default(false); // cannot be borrowed
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint
            $table->foreign('book_item_id')
                  ->references('id')
                  ->on('book_items')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('books');
    }
}
