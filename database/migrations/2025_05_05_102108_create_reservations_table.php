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
            $table->timestamp('ReservationDate')->useCurrent();
            $table->string('Status',20)->default('Pending');
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('student_id')
                  ->constrained('students')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('book_item_id')
                  ->constrained('book_items')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('library_branch_id')
                  ->constrained('library_branches')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
}
