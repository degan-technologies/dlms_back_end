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
            $table->string('code', 20);
            $table->string('location')->nullable();
            $table->integer('capacity')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('section_id')
                  ->nullable()
                  ->constrained('sections')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('library_branch_id')
                  ->constrained('library_branches')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            // Create a unique constraint on code + branch_id
            $table->unique(['code', 'library_branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shelves');
    }
}

