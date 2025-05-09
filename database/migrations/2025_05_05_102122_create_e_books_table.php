<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEBooksTable extends Migration
{
    public function up(): void
    {
        Schema::create('ebooks', function (Blueprint $table) {
            $table->foreignId('book_item_id')->primary();
            $table->string('file_url', 512);
            $table->string('file_format', 20)->nullable(); // PDF, EPUB, MOBI, etc.
            $table->float('file_size_mb')->nullable();
            $table->integer('pages')->nullable();
            $table->boolean('is_downloadable')->default(true);
            $table->boolean('requires_authentication')->default(true);
            $table->string('drm_type')->nullable(); // Digital Rights Management type
            $table->timestamp('access_expires_at')->nullable(); // For time-limited access
            $table->integer('max_downloads')->nullable(); // Maximum allowed downloads
            $table->string('reader_app')->nullable(); // Recommended reader application
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

    public function down(): void
    {
        Schema::dropIfExists('ebooks');
    }
}
