<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration {
    public function up(): void {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('adress', 255)->nullable();
            $table->string('grade')->nullable();
            $table->string('section')->nullable();
            $table->string('gender');
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

        });
    }

    public function down(): void {
        Schema::dropIfExists('students');
    }
}
