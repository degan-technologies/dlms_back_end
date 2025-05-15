<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\EBook;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookmarkSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding bookmarks...');        // Get necessary related models
        $users = User::all();
        $ebooks = EBook::all();

        // Check if we have the necessary data
        if ($users->isEmpty() || $ebooks->isEmpty()) {
            $this->command->warn('Missing required data for Bookmark seeder. Please seed related tables first.');
            return;
        }

        // Create bookmarks
        // Students are more likely to have bookmarks
        $studentUsers = $users->filter(function ($user) {
            return $user->hasRole('student');
        });

        if ($studentUsers->isEmpty()) {
            $studentUsers = $users; // Fallback to all users if no students
        }

        // Generate some sample titles
        $titles = [
            'Important section',
            'For assignment',
            'Key concept',
            'Reference for exam',
            'Interesting part',
            'Chapter summary',
            'Review later',
            'Useful information',
        ];

        // For each student, create 0-5 bookmarks
        foreach ($studentUsers as $user) {
            $bookmarkCount = rand(0, 5);
            
            // Get random ebooks to bookmark
            $userEbooks = $ebooks->random(min($bookmarkCount, $ebooks->count()));
            
            foreach ($userEbooks as $ebook) {                Bookmark::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'e_book_id' => $ebook->id,
                    ],
                    [
                        'title' => $titles[array_rand($titles)],
                    ]
                );
            }
        }
        
        $this->command->info('Bookmarks seeded successfully.');
    }
}
