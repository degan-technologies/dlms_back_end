<?php

namespace Database\Seeders;

use App\Models\BookItem;
use App\Models\Category;
use App\Models\Language;
use App\Models\Library;
use App\Models\Shelf;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class BookItemSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding book items...');

        // Get necessary related models
        $libraries = Library::all();
        $shelves = Shelf::all();
        $categories = Category::all();
        $languages = Language::all();
        $subjects = Subject::all();

        // Check if we have the necessary data
        if ($libraries->isEmpty() || $shelves->isEmpty() || $categories->isEmpty() || $languages->isEmpty() || $subjects->isEmpty()) {
            $this->command->warn('Missing required data for BookItem seeder. Please seed related tables first.');
            return;
        }

        // Sample book items data
        $bookItems = [
            [
                'title' => 'Introduction to Mathematics',
                'author' => 'John Smith',
                'description' => 'A comprehensive introduction to basic mathematics principles.',
                'cover_image_url' => 'https://example.com/images/math-intro.jpg',
                'grade' => 'Grade 9',
            ],
            [
                'title' => 'Advanced Physics',
                'author' => 'Maria Rodriguez',
                'description' => 'Explores advanced concepts in modern physics.',
                'cover_image_url' => 'https://example.com/images/physics-advanced.jpg',
                'grade' => 'Grade 12',
            ],
            [
                'title' => 'History of Ethiopia',
                'author' => 'Dawit Bekele',
                'description' => 'A detailed account of Ethiopian history and culture.',
                'cover_image_url' => 'https://example.com/images/ethiopia-history.jpg',
                'grade' => 'Grade 10',
            ],
            [
                'title' => 'Computer Programming Basics',
                'author' => 'Sarah Johnson',
                'description' => 'Learn the fundamentals of computer programming.',
                'cover_image_url' => 'https://example.com/images/programming.jpg',
                'grade' => 'Grade 11',
            ],
            [
                'title' => 'Biology and Life Sciences',
                'author' => 'Michael Chen',
                'description' => 'Introduction to biology and the study of living organisms.',
                'cover_image_url' => 'https://example.com/images/biology.jpg',
                'grade' => 'Grade 8',
            ],
        ];

        foreach ($bookItems as $item) {
            BookItem::firstOrCreate(
                ['title' => $item['title'], 'author' => $item['author']],
                [
                    'description' => $item['description'],
                    'cover_image_url' => $item['cover_image_url'],
                    'grade' => $item['grade'],
                    'library_id' => $libraries->random()->id,
                    'shelf_id' => $shelves->random()->id,
                    'category_id' => $categories->random()->id,
                    'language_id' => $languages->random()->id,
                    'subject_id' => $subjects->random()->id,
                ]
            );
        }
        
        $this->command->info('Book items seeded successfully.');
    }
}
