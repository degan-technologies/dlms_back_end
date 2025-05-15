<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookConditionsTable extends Migration {
    public function up(): void {
        Schema::create('book_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('condition'); 
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId(('book_id'))
                  ->constrained('books')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('book_conditions');
    }
}
