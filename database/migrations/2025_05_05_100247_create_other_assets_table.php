<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtherAssetsTable extends Migration {
    public function up(): void {
        Schema::create('other_assets', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('asset_type', 50)->nullable();
            $table->string('unique_id', 50)->nullable();
            $table->text('details')->nullable();
            $table->foreignId('book_item_id')
                ->constrained('book_items')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('other_assets');
    }
}
