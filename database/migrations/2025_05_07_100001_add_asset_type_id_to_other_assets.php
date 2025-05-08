<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddAssetTypeIdToOtherAssets extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('other_assets', function (Blueprint $table) {
            $table->foreignId('asset_type_id')->nullable()->after('book_item_id');
            
            // Add foreign key constraint
            $table->foreign('asset_type_id')
                  ->references('id')
                  ->on('asset_types')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
        
        // Migrate existing data by matching asset_type string with asset_types table name
        DB::statement("
            UPDATE other_assets oa
            INNER JOIN asset_types at ON oa.asset_type = at.name
            SET oa.asset_type_id = at.id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('other_assets', function (Blueprint $table) {
            $table->dropForeign(['asset_type_id']);
            $table->dropColumn('asset_type_id');
        });
    }
}