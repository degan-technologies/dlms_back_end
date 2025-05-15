<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShelvesTable extends Migration
{
    public function up(): void
    {
        Schema::create('shelves', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();


            $table->foreignId('library_id')
                  ->constrained('libraries')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shelves');
    }
}

