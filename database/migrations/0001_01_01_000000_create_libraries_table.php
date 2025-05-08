<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibrariesTable extends Migration
{
    public function up(): void
    {
        Schema::create('libraries', function (Blueprint $table) {
            $table->id(); // id
            $table->string('name');
            $table->string('address');
            $table->string('contact_number');
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at


        });
    }

    public function down(): void
    {
        Schema::dropIfExists('libraries');
    }
}
