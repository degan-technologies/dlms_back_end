<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('collection_ebook', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')
                ->constrained('collections')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreignId('e_book_id')
                ->constrained("e_books")
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('collection_ebook');
    }
};
