<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration for notification_types table

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_types')) {
            Schema::create('notification_types', function (Blueprint $table) {
                $table->id();
                $table->string('type')->unique();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_types');
    }
};
