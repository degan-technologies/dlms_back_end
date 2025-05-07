<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookConditionsTable extends Migration {
    public function up(): void {
        Schema::create('book_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('condition'); // e.g. New, Good, Damaged
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('book_item_id')->constrained('book_items')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('book_conditions');
    }
}
