<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtherAssetsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('other_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('book_item_id');
            $table->primary('book_item_id');
            $table->foreignId('asset_type_id')->constrained()->onDelete('restrict');
            $table->string('asset_type')->nullable()->comment('Deprecated: Use asset_type_id instead');
            $table->string('media_type')->nullable();
            $table->string('unique_id')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('physical_condition')->nullable();
            $table->string('location_details')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->text('usage_instructions')->nullable();
            $table->boolean('restricted_access')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('book_item_id')
                ->references('id')
                ->on('book_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_assets');
    }
}
