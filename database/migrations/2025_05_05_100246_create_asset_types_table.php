<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAssetTypesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_electronic')->default(false);
            $table->string('file_type_category')->nullable()->comment('document, image, video, audio, etc');
            $table->json('allowed_extensions')->nullable()->comment('["pdf","mp4","mp3","jpg"]');
            $table->integer('max_file_size')->nullable()->comment('Maximum file size in KB');
            $table->string('icon')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_types');
    }
}