<?php

namespace Database\Seeders;

use App\Models\AskLibrarian;
use App\Models\User;
use Illuminate\Database\Seeder;

class AskLibrarianSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding ask librarian questions...');

        // Get all users
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Sample questions
        $questions = [
            "How can I find books on quantum physics?",
            "I need help locating resources for my research paper on environmental sustainability.",
            "When will the library get new copies of the textbook for Biology 101?",
            "Can you recommend books similar to 'The Great Gatsby'?",
            "Do you have any resources on machine learning for beginners?",
            "I can't find the reference section for mathematics. Where is it located?",
            "Are there any quiet study rooms available for reservation?",
            "How do I access the online journal databases from home?",
            "What are the library's operating hours during holidays?",
            "Can you help me find primary sources for my history project?",
        ];

        // Sample responses (some will be null to simulate pending questions)
        $responses = [
            "You can find books on quantum physics in the Science section, shelves 24-26. We also have digital resources available through our online catalog.",
            "For environmental sustainability research, I recommend checking our Environmental Studies section on the second floor. We also have access to specialized journals through JSTOR and ScienceDirect.",
            null,
            "If you enjoyed 'The Great Gatsby', you might like other works by F. Scott Fitzgerald such as 'Tender Is the Night'. We also recommend 'The Sun Also Rises' by Ernest Hemingway or 'Mrs. Dalloway' by Virginia Woolf for similar themes and style.",
            null,
            "The mathematics reference section is located on the third floor, north wing, shelves 42-45.",
            "Yes, we have study rooms that can be reserved through the library website or at the front desk. They are available in 2-hour blocks.",
            null,
            "During holidays, the library operates from 10 AM to 4 PM. Please check our website for specific holidays when we may be closed entirely.",
            "For primary sources on history, I recommend checking our special collections department. We also have access to several historical archives and databases. Could you specify which historical period you're researching?",
        ];

        // Create ask librarian questions for each user
        foreach ($users as $user) {
            // Generate 0-3 questions per user
            $questionCount = rand(0, 3);
            
            for ($i = 0; $i < $questionCount; $i++) {
                $questionIndex = array_rand($questions);
                
                // 60% chance to have a response
                $response = (rand(1, 10) <= 6 && isset($responses[$questionIndex])) ? $responses[$questionIndex] : null;
                
                AskLibrarian::create([
                    'user_id' => $user->id,
                    'question' => $questions[$questionIndex],
                    'response' => $response,
                ]);
            }
        }
        
        $this->command->info('Ask librarian questions seeded successfully.');
    }
}
