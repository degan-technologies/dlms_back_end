<?php

namespace Database\Seeders;

use App\Models\EBook;
use App\Models\RecentlyViewed;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RecentlyViewedSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding recently viewed records...');        // Get necessary related models
        $users = User::all();
        $ebooks = EBook::all();

        // Check if we have the necessary data
        if ($users->isEmpty() || $ebooks->isEmpty()) {
            $this->command->warn('Missing required data for RecentlyViewed seeder. Please seed related tables first.');
            return;
        }

        // Create recently viewed records for users
        foreach ($users as $user) {
            // Generate 0-10 recently viewed entries per user
            $viewCount = rand(0, 10);
            
            // Get random ebooks for viewing history
            $userEbooks = $ebooks->random(min($viewCount, $ebooks->count()));
            
            foreach ($userEbooks as $ebook) {
                // Create a timestamp within the last 30 days
                $daysAgo = rand(0, 30);
                $viewedAt = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59));
                
                RecentlyViewed::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'e_book_id' => $ebook->id,
                    ],
                    [
                        'last_viewed_at' => $viewedAt,
                    ]
                );
            }
        }
        
        $this->command->info('Recently viewed records seeded successfully.');
    }
}
