<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('recently_vieweds', function (Blueprint $table) {
            $table->id();            $table->foreignId('user_id')->constrained("users")->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('e_book_id')->constrained("e_books")->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('last_viewed_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('recently_vieweds');
    }
};
