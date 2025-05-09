<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookItem;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all BookItems with item_type = physical
        $bookItems = BookItem::where('item_type', 'physical')->get();
        
        foreach ($bookItems as $bookItem) {
            // Create corresponding Book records with specific physical book details
            Book::updateOrCreate(
                ['book_item_id' => $bookItem->id],
                [
                    'book_item_id' => $bookItem->id,
                    'edition' => '1st Edition', // Default, can be updated later
                    'pages' => rand(150, 700), // Random page count for sample data
                    'cover_type' => array_rand(array_flip(['hardcover', 'paperback', 'spiral'])),
                    'dimensions' => '8.5 x 5.5 inches',
                    'weight_grams' => rand(200, 1200), // Random weight
                    'barcode' => 'BOOK' . str_pad($bookItem->id, 6, '0', STR_PAD_LEFT),
                    'shelf_location_detail' => 'Row ' . rand(1, 5) . ', Position ' . rand(1, 20),
                    'reference_only' => rand(0, 5) === 0, // 20% chance of being reference only
                ]
            );
        }
    }
}