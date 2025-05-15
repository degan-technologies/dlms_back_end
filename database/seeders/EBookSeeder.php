<?php

namespace Database\Seeders;

use App\Models\BookItem;
use App\Models\EBook;
use App\Models\EbookType;
use Illuminate\Database\Seeder;

class EbookSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding ebooks...');

        // Get necessary related models
        $bookItems = BookItem::all();
        $ebookTypes = EbookType::all();

        // Check if we have the necessary data
        if ($bookItems->isEmpty() || $ebookTypes->isEmpty()) {
            $this->command->warn('Missing required data for Ebook seeder. Please seed related tables first.');
            return;
        }

        // File formats
        $formats = ['PDF', 'EPUB', 'MOBI', 'AZW', 'DOC'];

        // For each book item, create an ebook version (for about 70% of books)
        foreach ($bookItems as $bookItem) {
            // 70% chance to create an ebook
            if (rand(1, 10) <= 7) {
                $format = $formats[array_rand($formats)];
                $title = str_replace(' ', '_', $bookItem->title);
                
                EBook::firstOrCreate(
                    [
                        'book_item_id' => $bookItem->id,
                    ],
                    [
                        'file_path' => "/storage/ebooks/{$title}.{$format}",
                        'file_format' => $format,
                        'file_name' => "{$title}.{$format}",
                        'isbn' => 'E-' . rand(1000000000, 9999999999),                        'file_size_mb' => rand(1, 50) + (rand(0, 99) / 100),
                        'pages' => rand(50, 600),
                        'is_downloadable' => rand(0, 10) > 3, // 70% chance to be downloadable
                        'e_book_type_id' => $ebookTypes->random()->id,
                    ]
                );
            }
        }
        
        $this->command->info('Ebooks seeded successfully.');
    }
}
