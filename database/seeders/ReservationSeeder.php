<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Library;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding book reservations...');

        // Get necessary related models
        // If you have a way to filter student users, do it here
        $users = User::all();
        $books = Book::where('is_borrowable', true)->get();
        $libraries = Library::all();

        if ($users->isEmpty() || $books->isEmpty() || $libraries->isEmpty()) {
            $this->command->warn('Missing required data for Reservation seeder. Please seed related tables first.');
            return;
        }

        $statuses = ['pending', 'approved', 'rejected', 'expired', 'fulfilled'];

        foreach ($users as $user) {
            $reservationCount = rand(0, 2);
            $userBooks = $books->random(min($reservationCount, $books->count()));

            foreach ($userBooks as $book) {
                $reservationCode = strtoupper(Str::random(8));
                $status = $statuses[array_rand($statuses)];
                $reservationDate = Carbon::now()->subDays(rand(1, 14));
                $expirationTime = rand(0, 1) ? $reservationDate->copy()->addDays(7) : null;

                \App\Models\Reservation::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'library_id' => $libraries->random()->id,
                    'reservation_date' => $reservationDate,
                    'expiration_time' => $expirationTime,
                    'status' => $status,
                    'reservation_code' => $reservationCode,
                ]);
            }
        }

        $this->command->info('Book reservations seeded successfully.');
    }
}
