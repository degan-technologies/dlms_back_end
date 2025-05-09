<?php

namespace Database\Seeders;

use App\Models\OtherAsset;
use App\Models\BookItem;
use App\Models\AssetType;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OtherAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all BookItems with item_type = other
        $bookItems = BookItem::where('item_type', 'other')->get();
        
        // Get available asset types
        $assetTypes = AssetType::where('is_active', true)->pluck('id')->toArray();
        
        if (empty($assetTypes)) {
            // If no asset types found, add a default ID for testing
            $assetTypes = [1];
        }
        
        foreach ($bookItems as $bookItem) {
            $mediaTypes = ['DVD', 'Audio CD', 'Map', 'Educational Kit', 'Journal', 'Database'];
            $conditions = ['excellent', 'good', 'fair', 'poor', 'damaged'];
            
            // Create corresponding OtherAsset records with specific details
            OtherAsset::updateOrCreate(
                ['book_item_id' => $bookItem->id],
                [
                    'book_item_id' => $bookItem->id,
                    'asset_type_id' => $assetTypes[array_rand($assetTypes)], // Random asset type from available types
                    'media_type' => $mediaTypes[array_rand($mediaTypes)], 
                    'unique_id' => 'ASSET-' . strtoupper(substr(md5($bookItem->id), 0, 8)),
                    'duration_minutes' => in_array($bookItem->title, ['Introduction to Machine Learning - Course Videos']) ? 
                        rand(120, 600) : null, // Duration for video content
                    'manufacturer' => rand(0, 1) ? 'Various Publishers' : null,
                    'physical_condition' => $conditions[array_rand($conditions)],
                    'location_details' => 'Storage ' . rand(1, 5) . ', Drawer ' . rand(1, 10),
                    'acquisition_date' => Carbon::now()->subMonths(rand(0, 36))->toDateString(),
                    'usage_instructions' => rand(0, 1) ? 'Handle with care. Return to library staff after use.' : null,
                    'restricted_access' => rand(0, 10) < 3, // 30% chance of restricted access
                ]
            );
        }
    }
}