<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('FirstName', 100);
            $table->string('LastName', 100);
            $table->string('email', 255)->nullable();
            $table->string('phone_no', 15)->nullable();
            $table->timestamps();
            $table->foreignId('user_id')
            ->constrained('users')
            ->onDelete('restrict')
            ->onUpdate('cascade');

      $table->foreignId('library_branch_id')
            ->constrained('library_branches')
            ->onDelete('restrict')
            ->onUpdate('cascade');
         
        });
    }

    public function down()
    {
        Schema::dropIfExists('admins');
    }
}