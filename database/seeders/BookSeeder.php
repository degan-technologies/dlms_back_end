<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookItem;
use App\Models\Library;
use App\Models\Shelf;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding books...');

        // Get necessary related models
        $bookItems = BookItem::all();
        $libraries = Library::all();
        $shelves = Shelf::all();

        // Check if we have the necessary data
        if ($bookItems->isEmpty() || $libraries->isEmpty() || $shelves->isEmpty()) {
            $this->command->warn('Missing required data for Book seeder. Please seed related tables first.');
            return;
        }

        // For each book item, create a few physical book copies
        foreach ($bookItems as $bookItem) {
            // Create 1 to 3 book copies for each book item
            $copies = rand(1, 3);
            
            for ($i = 0; $i < $copies; $i++) {
                Book::firstOrCreate(
                    [
                        'book_item_id' => $bookItem->id,
                        'isbn' => '978-' . rand(1000000000, 9999999999), // Generate random ISBN
                    ],
                    [
                        'edition' => rand(1, 5) . (rand(0, 1) ? 'th' : '') . ' Edition',
                        'pages' => rand(100, 500),
                        'is_borrowable' => rand(0, 10) > 2, // 80% chance to be borrowable
                        'library_id' => $libraries->random()->id,
                        'shelf_id' => $shelves->random()->id,
                    ]
                );
            }
        }
        
        $this->command->info('Books seeded successfully.');
    }
}
