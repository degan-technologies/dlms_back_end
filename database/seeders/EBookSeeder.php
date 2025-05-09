<?php

namespace Database\Seeders;

use App\Models\EBook;
use App\Models\BookItem;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all BookItems with item_type = ebook
        $bookItems = BookItem::where('item_type', 'ebook')->get();
        
        foreach ($bookItems as $bookItem) {
            // Create corresponding EBook records with specific ebook details
            EBook::updateOrCreate(
                ['book_item_id' => $bookItem->id],
                [
                    'book_item_id' => $bookItem->id,
                    'file_url' => 'https://library.example.com/ebooks/' . $bookItem->id . '.pdf',
                    'file_format' => array_rand(array_flip(['pdf', 'epub', 'mobi'])),
                    'file_size_mb' => rand(5, 25) + (rand(0, 99) / 100), // Random size between 5-25 MB
                    'pages' => rand(100, 600), // Random page count
                    'is_downloadable' => rand(0, 1) === 1, // 50% chance of being downloadable
                    'requires_authentication' => true, // Most ebooks require authentication
                    'drm_type' => rand(0, 1) === 1 ? 'Adobe DRM' : null,
                    'access_expires_at' => rand(0, 3) === 0 ? Carbon::now()->addMonths(rand(1, 12)) : null, // Some have expiration
                    'max_downloads' => rand(0, 2) === 0 ? rand(1, 5) : null, // Some have download limits
                    'reader_app' => $this->getRandomReaderApp(), // Using a helper method instead of array_flip with null
                ]
            );
        }
    }

    /**
     * Helper method to get a random reader app with possibility of null
     * 
     * @return string|null
     */
    private function getRandomReaderApp()
    {
        $apps = ['Adobe Digital Editions', 'Kindle', 'Libby', null];
        return $apps[array_rand($apps)];
    }
}