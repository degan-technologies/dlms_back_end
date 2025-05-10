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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('notable'); // For EBook or OtherAsset
            $table->text('content');
            $table->integer('page_number')->nullable();
            $table->string('position')->nullable(); // Can store coordinates or percentage
            $table->text('highlight_text')->nullable(); // Text that is highlighted
            $table->string('color')->nullable(); // Color of the note/highlight
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
