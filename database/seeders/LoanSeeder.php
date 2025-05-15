<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Library;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding book loans...');

        // Get necessary related models
        $users = User::all();
        $books = Book::where('is_borrowable', true)->get();
        $libraries = Library::all();

        // Check if we have the necessary data
        if ($users->isEmpty() || $books->isEmpty() || $libraries->isEmpty()) {
            $this->command->warn('Missing required data for Loan seeder. Please seed related tables first.');
            return;
        }

        // Create loans
        $eligibleUsers = $users->filter(function ($user) {
            return $user->hasRole('student') || $user->hasRole('teacher');
        });

        if ($eligibleUsers->isEmpty()) {
            $eligibleUsers = $users; // Fallback to all users if no eligible users
        }

        foreach ($eligibleUsers as $user) {
            $loanCount = rand(0, 3);
            $userBooks = $books->random(min($loanCount, $books->count()));

            foreach ($userBooks as $book) {
                $loanStatus = rand(1, 3);

                $borrowDate = Carbon::now()->subDays(rand(5, 30));
                $dueDate = (clone $borrowDate)->addDays(14);

                $returnedDate = null;
                if ($loanStatus == 2) {
                    $returnedDate = (clone $borrowDate)->addDays(rand(1, 10));
                }

                Loan::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'library_id' => $libraries->random()->id,
                    'borrow_date' => $borrowDate->toDateString(),
                    'due_date' => $dueDate->toDateString(),
                    'returned_date' => $returnedDate?->toDateString(),
                ]);
            }
        }

        $this->command->info('Book loans seeded successfully.');
    }
}
