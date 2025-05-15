<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibrariesTable extends Migration
{
    public function up(): void
    {
        Schema::create('libraries', function (Blueprint $table) {
            $table->id(); 
            $table->string('name');
            $table->string('contact_number');
            $table->foreignId('library_branch_id')->constrained('library_branches')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps(); 
            $table->softDeletes(); 

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('libraries');
    }
}
