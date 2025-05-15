<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinesTable extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id(); 
            $table->decimal('fine_amount',10,2);
            $table->date('fine_date');
            $table->string('reason')->nullable();
            $table->date('payment_date')->nullable();
            $table->boolean('payment_status')->default(false);
            $table->string('receipt_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('library_id')
                  ->constrained('libraries')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('loan_id')
                  ->constrained('loans')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
}

