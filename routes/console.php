<?php
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Schedule::call(function () {
    Log::info('Scheduler is running at: ' . now());
    file_put_contents(storage_path('logs/scheduler-debug.log'), 'Scheduler ran at: '.now().PHP_EOL, FILE_APPEND);
})->everyMinute();

Schedule::command('loans:check --type=overdue')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/loans-overdue.log'));

Schedule::command('loans:check --type=upcoming')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/loans-upcoming.log'));
