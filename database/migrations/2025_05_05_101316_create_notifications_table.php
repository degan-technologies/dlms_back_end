<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->timestamps();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('notification_type_id')
                  ->constrained('notification_types')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
}

