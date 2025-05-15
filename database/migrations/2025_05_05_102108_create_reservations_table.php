<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->timestamp('reservation_date')->useCurrent();
            $table->string('status',20)->default('pending');
           
            $table->timestamp('expiration_time')->nullable();
            $table->string('reservation_code', 50)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('book_id')
                  ->constrained('books')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('library_id')
                  ->constrained('libraries')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
}
