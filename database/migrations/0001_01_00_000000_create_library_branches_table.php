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
            $table->string('address');
            $table->string('location');
            $table->string('contact_number', 20);
            $table->string('email');
            $table->json('library_time')->nullable();

           
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('library_branches');
    }
}
