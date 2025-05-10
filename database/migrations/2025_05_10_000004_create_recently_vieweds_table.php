<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recently_vieweds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_item_id')->constrained()->onDelete('cascade');
            $table->timestamp('last_viewed_at')->useCurrent();
            $table->integer('view_count')->default(1);
            $table->integer('last_page_viewed')->nullable();
            $table->integer('view_duration')->nullable()->comment('Duration in seconds');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Ensure each user has only one recently viewed entry per book item
            $table->unique(['user_id', 'book_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recently_vieweds');
    }
};
