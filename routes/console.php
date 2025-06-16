<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule as ConsoleSchedule;

Schedule::call(function () {
    Log::info('Scheduler is running at: ' . now());
    file_put_contents(storage_path('logs/scheduler-debug.log'), 'Scheduler ran at: ' . now() . PHP_EOL, FILE_APPEND);
})->everyMinute();

Schedule::command('loans:check --type=overdue')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/loans-overdue.log'));

Schedule::command('loans:check --type=upcoming')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/loans-upcoming.log'));

// Schedule: Expire reservations after 1 hour and free the book
Schedule::call(function () {
    $expired = \App\Models\Reservation::where('status', 'pending')
        ->where('created_at', '<', now()->subMinutes(5))
        ->get();
    foreach ($expired as $reservation) {
        $reservation->status = 'expired';
        $reservation->save();
        // Set the related book's is_reserved to false
        if ($reservation->book_id) {
            $book = \App\Models\Book::find($reservation->book_id);
            if ($book) {
                $book->is_reserved = false;
                $book->save();
            }
        }
    }
})->everyMinute();
