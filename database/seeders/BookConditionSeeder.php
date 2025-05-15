<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCondition;
use Illuminate\Database\Seeder;

class BookConditionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding book conditions...');

        // Get all books
        $books = Book::all();

        if ($books->isEmpty()) {
            $this->command->warn('No books found. Please run BookSeeder first.');
            return;
        }

        // Possible conditions
        $conditions = [
            'New' => 'Book is in pristine condition.',
            'Good' => 'Book is in good condition with minimal wear.',
            'Fair' => 'Book shows some signs of wear but all pages are intact.',
            'Poor' => 'Book has significant wear and may have some damage.',
            'Damaged' => 'Book has damage that may affect usability.',
        ];

        // Assign conditions to books
        foreach ($books as $book) {
            // Select a random condition
            $condition = array_rand($conditions);
            
            BookCondition::firstOrCreate(
                [
                    'book_id' => $book->id,
                ],
                [
                    'condition' => $condition,
                    'note' => $conditions[$condition],
                ]
            );
        }
        
        $this->command->info('Book conditions seeded successfully.');
    }
}
