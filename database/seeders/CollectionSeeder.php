<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\EBook;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding collections and collection-ebook associations...');        // Get all ebooks
        $ebooks = EBook::all();

        if ($ebooks->isEmpty()) {
            $this->command->warn('No ebooks found. Please run EbookSeeder first.');
            return;
        }

        // Create collections
        $collections = [
            'Academic Resources',
            'Science Fiction',
            'Mathematics',
            'Physics',
            'Chemistry',
            'Literature',
            'History',
            'Computer Science',
            'Biology',
            'Educational'
        ];

        $createdCollections = [];

        foreach ($collections as $collectionName) {
            $collection = Collection::firstOrCreate(['name' => $collectionName]);
            $createdCollections[] = $collection;

            // Only assign if there are at least 1 ebook
            if ($ebooks->count() >= 2) {
                $collectionEbooks = $ebooks->random(rand(2, min(5, $ebooks->count())));

                foreach ($collectionEbooks as $ebook) {
                    // Use attach method to create the relationship in the pivot table
                    $collection->ebooks()->syncWithoutDetaching([$ebook->id]);
                }
            } elseif ($ebooks->count() === 1) {
                $collection->ebooks()->syncWithoutDetaching([$ebooks->first()->id]);
            }
        }

        $this->command->info('Collections and collection-ebook associations seeded successfully.');
    }
}
