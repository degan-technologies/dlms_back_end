<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration {
    public function up() {
        Schema::create('books', function (Blueprint $table) {
            $table->string('isbn', 20)->primary();
            $table->string('title');
            $table->smallInteger('publication_year')->nullable();
            $table->string('edition')->nullable();
            $table->foreignId(('book_item_id'))->constrained('book_items')->onDelete('restrict')->onUpdate('cascade');  
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('books');
    }
}
