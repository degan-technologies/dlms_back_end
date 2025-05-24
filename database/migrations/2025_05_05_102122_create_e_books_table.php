<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEBooksTable extends Migration
{
    public function up(): void
    {
        Schema::create('e_books', function (Blueprint $table) {
            $table->id();
            $table->string('file_path', 512);
            $table->string('file_format', 20)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->string('isbn', 20)->nullable();
            $table->float('file_size_mb')->nullable();
            $table->integer('pages')->nullable();
            $table->boolean('is_downloadable')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Foreign key to the user who uploaded the e-book
            $table->foreignId('user_id');
                //   ->constrained('users');


            // Foreign key constraint
            $table->foreignId('book_item_id');
                //   ->references('id')
                //   ->on('book_items');
                //   ->onDelete('cascade')
                //   ->onUpdate('cascade');

            $table->foreignId('e_book_type_id');
                //   ->constrained('e_book_types');
                //   ->onDelete('restrict')
                //   ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_books');
    }
}
