<?php

namespace Database\Seeders;

use App\Models\EBook;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding notes...');        // Get necessary related models
        $users = User::all();
        $ebooks = EBook::all();

        // Check if we have the necessary data
        if ($users->isEmpty() || $ebooks->isEmpty()) {
            $this->command->warn('Missing required data for Note seeder. Please seed related tables first.');
            return;
        }

        // Sample note contents
        $noteContents = [
            "This is an important definition that I need to remember.",
            "Key formula for the exam.",
            "Need to ask teacher about this concept.",
            "This explains the whole chapter very well.",
            "Example problems to review.",
            "Similar to what we discussed in class.",
            "Concepts to review before the test.",
            "Important historical fact.",
            "This contradicts what we learned earlier.",
            "References other helpful resources.",
        ];

        // Sample highlighted texts
        $highlightTexts = [
            "The concept of relativity states that...",
            "According to the theory of evolution...",
            "The primary function of mitochondria is...",
            "The quadratic formula can be derived by...",
            "Historical evidence suggests that...",
            "In computer science, algorithms are defined as...",
            "The literary devices used in this passage...",
            "Economic principles indicate that...",
            "Chemical reactions occur when...",
            "Mathematical induction proves that...",
        ];

        // Create notes for students
        $studentUsers = $users->filter(function ($user) {
            return $user->hasRole('student');
        });

        if ($studentUsers->isEmpty()) {
            $studentUsers = $users; // Fallback to all users if no students
        }

        // For each student, create 0-8 notes
        foreach ($studentUsers as $user) {
            $noteCount = rand(0, 8);
            
            // Get random ebooks for notes
            $userEbooks = $ebooks->random(min($noteCount, $ebooks->count()));
            
            foreach ($userEbooks as $ebook) {
                // 50% chance to include highlight text
                $includeHighlight = (rand(0, 1) == 1);
                  Note::create([
                    'user_id' => $user->id,
                    'e_book_id' => $ebook->id,
                    'content' => $noteContents[array_rand($noteContents)],
                    'page_number' => rand(1, $ebook->pages ?: 100),
                    'highlight_text' => $includeHighlight ? $highlightTexts[array_rand($highlightTexts)] : null,
                ]);
            }
        }
        
        $this->command->info('Notes seeded successfully.');
    }
}
