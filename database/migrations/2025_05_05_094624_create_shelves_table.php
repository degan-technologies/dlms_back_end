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
            $table->string('ShelfCode',20)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('section_id')
                  ->constrained('sections')
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
        Schema::dropIfExists('shelves');
    }
}

