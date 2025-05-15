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
        // Primarily for students and staff
        $eligibleUsers = $users->filter(function ($user) {
            return $user->hasRole('student') || $user->hasRole('staff');
        });

        if ($eligibleUsers->isEmpty()) {
            $eligibleUsers = $users; // Fallback to all users if no eligible users
        }

        // For demonstration purposes, create a mix of active loans, returned loans, and overdue loans
        foreach ($eligibleUsers as $user) {
            // Generate 0-3 loans per user
            $loanCount = rand(0, 3);
            
            // Get random books to loan
            $userBooks = $books->random(min($loanCount, $books->count()));
            
            foreach ($userBooks as $book) {
                // Loan status:
                // 1. Active loan (not returned yet, not overdue)
                // 2. Returned loan
                // 3. Overdue loan (not returned yet, past due date)
                $loanStatus = rand(1, 3);
                
                $borrowDate = Carbon::now()->subDays(rand(5, 30)); // 5-30 days ago
                $dueDate = (clone $borrowDate)->addDays(14); // 14 days loan period
                
                $returnedDate = null;
                if ($loanStatus == 2) {
                    // Returned loan (returned 1-10 days after borrowing, but before due date)
                    $returnedDate = (clone $borrowDate)->addDays(rand(1, 10));
                }
                
                Loan::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'library_id' => $libraries->random()->id,
                    'borrow_date' => $borrowDate,
                    'due_date' => $dueDate,
                    'returned_date' => $returnedDate,
                ]);
            }
        }
        
        $this->command->info('Book loans seeded successfully.');
    }
}
