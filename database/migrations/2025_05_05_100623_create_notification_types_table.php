<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTypesTable extends Migration
{
public function up(): void
{
    Schema::create('notifications', function (Blueprint $table) {
        // Use UUIDs for the primary key:
        $table->uuid('id')->primary();

        // Notification class name (e.g. App\Notifications\LoanStatusAlert)
        $table->string('type');

        // Polymorphic relation: which model was notified
        $table->morphs('notifiable');

        // JSON payload for your notification data
        $table->text('data');

        // Mark when (if ever) the user read it
        $table->timestamp('read_at')->nullable();

        // Timestamps for created_at / updated_at
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('notifications');
}

}
