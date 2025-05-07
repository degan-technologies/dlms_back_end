<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEBooksTable extends Migration
{
    public function up(): void
    {
        Schema::create('ebooks', function (Blueprint $table) {
            $table->id();
            $table->string('author', 255)->nullable();
            $table->string('isbn', 20);
            $table->string('file_url', 512);
            $table->string('file_format', 10)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('book_item_id')->constrained('book_items')->onDelete('restrict')->onUpdate('cascade');
           
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebooks');
    }
}
