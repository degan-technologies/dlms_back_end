<?php

namespace Database\Seeders;

use App\Models\BookItem;
use App\Models\Book;
use App\Models\EBook;
use App\Models\Category;
use App\Models\Language;
use App\Models\Library;
use App\Models\Shelf;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class BookItemSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding book items...');

        $libraries = Library::all();
        $shelves = Shelf::all();
        $categories = Category::all();
        $languages = Language::all();
        $subjects = Subject::all();
        $grades = \App\Models\Grade::all();
        $users = \App\Models\User::all();

        if ($libraries->isEmpty() || $shelves->isEmpty() || $categories->isEmpty() || $languages->isEmpty() || $subjects->isEmpty()) {
            $this->command->warn('Missing required data for BookItem seeder. Please seed related tables first.');
            return;
        }

        // 5 physical books
        $physicalBooks = [
            [
                'title' => 'Modern Chemistry',
                'author' => 'Jane Doe',
                'description' => 'A modern approach to chemistry.',
                'cover_image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb',
                'grade' => 'Grade 11',
            ],
            [
                'title' => 'World Geography',
                'author' => 'Alex Turner',
                'description' => 'Explore the world and its geography.',
                'cover_image' => 'https://images.unsplash.com/photo-1465101046530-73398c7f28ca',
                'grade' => 'Grade 10',
            ],
            [
                'title' => 'English Literature',
                'author' => 'Emily Bronte',
                'description' => 'Classic English literature studies.',
                'cover_image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794',
                'grade' => 'Grade 12',
            ],
            [
                'title' => 'Basic Algebra',
                'author' => 'Robert Brown',
                'description' => 'Algebra for beginners.',
                'cover_image' => 'https://images.unsplash.com/photo-1464983953574-0892a716854b',
                'grade' => 'Grade 9',
            ],
            [
                'title' => 'African History',
                'author' => 'Nia Okoye',
                'description' => 'A journey through African history.',
                'cover_image' => 'https://images.unsplash.com/photo-1503676382389-4809596d5290',
                'grade' => 'Grade 8',
            ],
        ];

        // 5 ebooks with specific type and file paths
        $ebooks = [
            [
                'title' => 'Digital Mathematics',
                'author' => 'Alan Turing',
                'description' => 'Mathematics in the digital age.',
                'cover_image' => 'https://images.unsplash.com/photo-1519125323398-675f0ddb6308',
                'grade' => 'Grade 11',
                'e_book_type_id' => 1,
                'file_path' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                'file_name' => 'digital_mathematics.pdf',
            ],
            [
                'title' => 'Physics Video Lessons',
                'author' => 'Isaac Newton',
                'description' => 'Physics explained with videos.',
                'cover_image' => 'https://images.unsplash.com/photo-1465101178521-c1a9136a3b99',
                'grade' => 'Grade 12',
                'e_book_type_id' => 2,
                'file_path' => 'https://www.youtube.com/embed/1Fi2b8Qj4Lk',
                'file_name' => 'physics_video_lessons',
            ],
            [
                'title' => 'Biology Audio Guide',
                'author' => 'Charles Darwin',
                'description' => 'Audio guide for biology.',
                'cover_image' => 'https://images.unsplash.com/photo-1465101046530-73398c7f28ca',
                'grade' => 'Grade 10',
                'e_book_type_id' => 2, // changed from 3 to 2
                'file_path' => 'https://www.youtube.com/embed/2Vv-BfVoq4g',
                'file_name' => 'biology_audio_guide',
            ],
            [
                'title' => 'Programming with Python',
                'author' => 'Guido van Rossum',
                'description' => 'Learn Python programming.',
                'cover_image' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353',
                'grade' => 'Grade 12',
                'e_book_type_id' => 1,
                'file_path' => 'https://www.orimi.com/pdf-test.pdf',
                'file_name' => 'programming_with_python.pdf',
            ],
            [
                'title' => 'Music Theory Audio',
                'author' => 'Ludwig Beethoven',
                'description' => 'Audio lessons on music theory.',
                'cover_image' => 'https://images.unsplash.com/photo-1465101178521-c1a9136a3b99',
                'grade' => 'Grade 9',
                'e_book_type_id' => 2, // changed from 3 to 2
                'file_path' => 'https://www.youtube.com/embed/JGwWNGJdvx8',
                'file_name' => 'music_theory_audio',
            ],
        ];

        // Seed 5 physical BookItems and Books
        foreach ($physicalBooks as $physicalBook) {
            $grade = $grades->where('name', $physicalBook['grade'])->first() ?? $grades->random();
            $library = $libraries->random();
            $category = $categories->random();
            $language = $languages->random();
            $subject = $subjects->random();
            $user = $users->random();

            // Create BookItem
            $bookItem = BookItem::create([
                'title' => $physicalBook['title'],
                'author' => $physicalBook['author'],
                'description' => $physicalBook['description'],
                'cover_image' => $physicalBook['cover_image'],
                'grade_id' => $grade->id,
                'library_id' => $library->id,
                'category_id' => $category->id,
                'language_id' => $language->id,
                'subject_id' => $subject->id,
                'user_id' => $user->id,
            ]);

            // Create a single Book for this BookItem
            Book::create([
                'book_item_id' => $bookItem->id,
                'title' => $physicalBook['title'],
                'user_id' => $user->id,
                'cover_image' => $physicalBook['cover_image'],
                'edition' => '1st Edition',
                'pages' => rand(100, 500),
                'is_borrowable' => 1,
                'is_reserved' => 0,
                'library_id' => $library->id,
                'shelf_id' => $shelves->random()->id,
                'publication_year' => now()->subYears(rand(0, 10))->format('Y'),
            ]);
        }

        // Seed 5 EBook BookItems and EBooks
        foreach ($ebooks as $ebook) {
            $grade = $grades->where('name', $ebook['grade'])->first() ?? $grades->random();
            $library = $libraries->random();
            $category = $categories->random();
            $language = $languages->random();
            $subject = $subjects->random();
            $user = $users->random();

            // Create BookItem
            $bookItem = BookItem::create([
                'title' => $ebook['title'],
                'author' => $ebook['author'],
                'description' => $ebook['description'],
                'cover_image' => $ebook['cover_image'],
                'grade_id' => $grade->id,
                'library_id' => $library->id,
                'category_id' => $category->id,
                'language_id' => $language->id,
                'subject_id' => $subject->id,
                'user_id' => $user->id,
            ]);

            // Create a single EBook for this BookItem
            EBook::create([
                'book_item_id' => $bookItem->id,
                'file_path' => $ebook['file_path'],
                'file_name' => $ebook['file_name'],
                'file_size_mb' => rand(1, 50) + (rand(0, 99) / 100),
                'pages' => isset($ebook['e_book_type_id']) && $ebook['e_book_type_id'] == 1 ? rand(50, 600) : null,
                'is_downloadable' => 1,
                'e_book_type_id' => $ebook['e_book_type_id'],
                'user_id' => $user->id,
            ]);
        }

        $this->command->info('Book items, books, and ebooks seeded successfully.');
    }
}
