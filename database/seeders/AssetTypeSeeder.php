<?php

namespace Database\Seeders;

use App\Models\AssetType;
use Illuminate\Database\Seeder;

class AssetTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $assetTypes = [
            [
                'name' => 'DVD',
                'description' => 'Digital Versatile Disc containing video content',
                'is_electronic' => false,
                'file_type_category' => null,
                'allowed_extensions' => null,
                'max_file_size' => null,
                'icon' => 'dvd-icon.png',
                'is_active' => true,
            ],
            [
                'name' => 'Audio CD',
                'description' => 'Compact Disc containing audio content',
                'is_electronic' => false,
                'file_type_category' => null,
                'allowed_extensions' => null,
                'max_file_size' => null,
                'icon' => 'cd-icon.png',
                'is_active' => true,
            ],
            [
                'name' => 'Digital Video',
                'description' => 'Electronic video files such as MP4, MOV, etc.',
                'is_electronic' => true,
                'file_type_category' => 'video',
                'allowed_extensions' => json_encode(['mp4', 'mov', 'avi', 'mkv']),
                'max_file_size' => 5242880, // 5GB in KB
                'icon' => 'video-icon.png',
                'is_active' => true,
            ],
            [
                'name' => 'Digital Audio',
                'description' => 'Electronic audio files such as MP3, WAV, etc.',
                'is_electronic' => true,
                'file_type_category' => 'audio',
                'allowed_extensions' => json_encode(['mp3', 'wav', 'ogg', 'flac']),
                'max_file_size' => 512000, // 500MB in KB
                'icon' => 'audio-icon.png',
                'is_active' => true,
            ],
            [
                'name' => 'Map',
                'description' => 'Physical maps and atlases',
                'is_electronic' => false,
                'file_type_category' => null,
                'allowed_extensions' => null,
                'max_file_size' => null,
                'icon' => 'map-icon.png',
                'is_active' => true,
            ],
            [
                'name' => 'Educational Kit',
                'description' => 'Educational materials packaged as kits',
                'is_electronic' => false,
                'file_type_category' => null,
                'allowed_extensions' => null,
                'max_file_size' => null,
                'icon' => 'kit-icon.png',
                'is_active' => true,
            ],
            [
                'name' => 'Board Game',
                'description' => 'Board games available for borrowing',
                'is_electronic' => false,
                'file_type_category' => null,
                'allowed_extensions' => null,
                'max_file_size' => null,
                'icon' => 'game-icon.png',
                'is_active' => true,
            ],
            [
                'name' => 'Journal',
                'description' => 'Physical academic or special interest journals',
                'is_electronic' => false,
                'file_type_category' => null,
                'allowed_extensions' => null,
                'max_file_size' => null,
                'icon' => 'journal-icon.png',
                'is_active' => true,
            ],
            [
                'name' => 'Digital Journal',
                'description' => 'Electronic academic or special interest journals',
                'is_electronic' => true,
                'file_type_category' => 'document',
                'allowed_extensions' => json_encode(['pdf', 'epub']),
                'max_file_size' => 102400, // 100MB in KB
                'icon' => 'digital-journal-icon.png',
                'is_active' => true,
            ],
            [
                'name' => 'Equipment',
                'description' => 'Technical equipment available for borrowing',
                'is_electronic' => false,
                'file_type_category' => null,
                'allowed_extensions' => null,
                'max_file_size' => null,
                'icon' => 'equipment-icon.png',
                'is_active' => true,
            ],
        ];

        foreach ($assetTypes as $assetType) {
            AssetType::updateOrCreate(
                ['name' => $assetType['name']],
                $assetType
            );
        }
    }
}