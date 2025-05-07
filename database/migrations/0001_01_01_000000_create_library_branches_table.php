<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibraryBranchesTable extends Migration
{
    public function up()
    {
        Schema::create('library_branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
            $table->string('address')->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('opening_hours')->nullable();
            $table->foreignId('library_id')->constrained('libraries')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('library_branches');
    }
}
