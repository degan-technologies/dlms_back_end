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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();            $table->foreignId('user_id')->constrained("users")->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('e_book_id')->constrained("e_books")->onDelete('restrict')->onUpdate('cascade');
            $table->text('highlight_text')->nullable(); 
            $table->longText('question');
            $table->longText('ai_response');
            $table->integer('page_number')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
