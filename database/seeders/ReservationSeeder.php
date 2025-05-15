<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Library;
use App\Models\Reservation;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding book reservations...');

        // Get necessary related models
        $students = Student::all();
        $books = Book::where('is_borrowable', true)->get();
        $libraries = Library::all();

        // Check if we have the necessary data
        if ($students->isEmpty() || $books->isEmpty() || $libraries->isEmpty()) {
            $this->command->warn('Missing required data for Reservation seeder. Please seed related tables first.');
            return;
        }

        // Reservation statuses
        $statuses = ['pending', 'approved', 'rejected', 'expired', 'fulfilled'];

        // Create reservations
        foreach ($students as $student) {
            // Generate 0-2 reservations per student
            $reservationCount = rand(0, 2);
            
            // Get random books to reserve
            $studentBooks = $books->random(min($reservationCount, $books->count()));
            
            foreach ($studentBooks as $book) {
                // Generate a unique reservation code
                $reservationCode = strtoupper(Str::random(8));
                
                // Pick a random status
                $status = $statuses[array_rand($statuses)];
                
                // Create the reservation date (1-14 days ago)
                $reservationDate = Carbon::now()->subDays(rand(1, 14));
                
                Reservation::create([
                    'student_id' => $student->id,
                    'book_id' => $book->id,
                    'library_id' => $libraries->random()->id,
                    'reservation_date' => $reservationDate,
                    'status' => $status,
                    'reservation_code' => $reservationCode,
                ]);
            }
        }
        
        $this->command->info('Book reservations seeded successfully.');
    }
}
