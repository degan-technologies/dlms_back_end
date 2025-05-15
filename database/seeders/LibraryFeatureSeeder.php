<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\EBook;
use App\Models\Note;
use App\Models\OtherAsset;
use App\Models\ReadingList;
use App\Models\RecentlyViewed;
use App\Models\User;
use App\Models\BookItem;
use Illuminate\Database\Seeder;

class LibraryFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users, ebooks, and other assets for creating relationships
        $users = User::take(5)->get();
        $ebooks = EBook::take(5)->get();
        $otherAssets = OtherAsset::take(3)->get();
        $bookItems = BookItem::take(10)->get();
        
        if ($users->isEmpty() || $ebooks->isEmpty() || $otherAssets->isEmpty() || $bookItems->isEmpty()) {
            $this->command->warn('Required data is missing. Make sure you have users, ebooks, and other assets seeded.');
            return;
        }
        
        // Create bookmarks
        foreach ($users as $user) {
            // Bookmarks for ebooks
            foreach ($ebooks as $index => $ebook) {
                if ($index > 2) break; // Limit to 3 bookmarks per user
                
                Bookmark::create([
                    'user_id' => $user->id,
                    'bookmarkable_id' => $ebook->book_item_id,
                    'bookmarkable_type' => EBook::class,
                    'page_number' => rand(1, 100),
                    'position' => '0.' . rand(100, 900),
                    'title' => "Bookmark for " . substr($ebook->bookItem->title ?? 'EBook', 0, 30),
                    'description' => "Important section on page " . rand(1, 100),
                ]);
            }
            
            // Bookmarks for other assets
            foreach ($otherAssets as $index => $asset) {
                if ($index > 1) break; // Limit to 2 bookmarks per user
                
                Bookmark::create([
                    'user_id' => $user->id,
                    'bookmarkable_id' => $asset->book_item_id,
                    'bookmarkable_type' => OtherAsset::class,
                    'position' => rand(10, 90) . '%',
                    'title' => "Bookmark for " . substr($asset->bookItem->title ?? 'Asset', 0, 30),
                    'description' => "Notable section at position " . rand(10, 90) . '%',
                ]);
            }
        }
        
        // Create notes
        foreach ($users as $user) {
            // Notes for ebooks
            foreach ($ebooks as $index => $ebook) {
                if ($index > 1) break; // Limit to 2 notes per user
                
                Note::create([
                    'user_id' => $user->id,
                    'notable_id' => $ebook->book_item_id,
                    'notable_type' => EBook::class,
                    'content' => "This section discusses important concepts about " . $ebook->bookItem->title ?? 'the topic',
                    'page_number' => rand(1, 100),
                    'position' => '0.' . rand(100, 900),
                    'highlight_text' => "Important concept highlighted by " . $user->username,
                    'color' => ['#FFEB3B', '#4CAF50', '#2196F3', '#FF9800'][rand(0, 3)],
                ]);
            }
            
            // Notes for other assets
            foreach ($otherAssets as $index => $asset) {
                if ($index > 0) break; // Limit to 1 note per user
                
                Note::create([
                    'user_id' => $user->id,
                    'notable_id' => $asset->book_item_id,
                    'notable_type' => OtherAsset::class,
                    'content' => "Important note about " . $asset->bookItem->title ?? 'this asset',
                    'position' => rand(10, 90) . '%',
                    'color' => ['#FFEB3B', '#4CAF50', '#2196F3', '#FF9800'][rand(0, 3)],
                ]);
            }
        }
        
        // Create reading lists
        foreach ($users as $user) {
            // Create 1-2 reading lists per user
            for ($i = 0; $i < rand(1, 2); $i++) {
                $readingList = ReadingList::create([
                    'user_id' => $user->id,
                    'title' => ['My Favorites', 'Must Read', 'Study Materials', 'Research Resources', 'Course References'][rand(0, 4)] . ' by ' . $user->username,
                    'description' => 'A collection of resources for ' . ['research', 'study', 'reference', 'enjoyment'][rand(0, 3)],
                    'is_public' => (bool)rand(0, 1),
                ]);
                
                // Add 3-5 book items to each reading list
                $randomBookItems = $bookItems->random(rand(3, min(5, $bookItems->count())));
                foreach ($randomBookItems as $bookItem) {
                    $readingList->bookItems()->attach($bookItem->id, [
                        'added_at' => now()->subDays(rand(1, 30)),
                        'notes' => rand(0, 1) ? 'Important for chapter ' . rand(1, 10) : null,
                    ]);
                }
            }
        }
        
        // Create recently viewed
        foreach ($users as $user) {
            // Add 5-8 recently viewed items per user
            $randomBookItems = $bookItems->random(rand(5, min(8, $bookItems->count())));
            foreach ($randomBookItems as $index => $bookItem) {
                RecentlyViewed::create([
                    'user_id' => $user->id,
                    'book_item_id' => $bookItem->id,
                    'last_viewed_at' => now()->subDays(rand(0, 14))->subHours(rand(0, 23)),
                    'view_count' => rand(1, 10),
                    'last_page_viewed' => ($bookItem->item_type === 'ebook') ? rand(1, 100) : null,
                    'view_duration' => rand(30, 3600), // 30 seconds to 1 hour
                ]);
            }
        }
        
        $this->command->info('Library features (bookmarks, notes, reading lists, recently viewed) seeded successfully.');
    }
}
