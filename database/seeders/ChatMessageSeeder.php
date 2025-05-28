<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\EBook;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatMessageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding chat messages...');        // Get necessary related models
        $users = User::all();
        $ebooks = EBook::all();

        // Check if we have the necessary data
        if ($users->isEmpty() || $ebooks->isEmpty()) {
            $this->command->warn('Missing required data for ChatMessage seeder. Please seed related tables first.');
            return;
        }

        // Sample questions
        $questions = [
            "Can you explain the concept of gravity in simple terms?",
            "What is the significance of this chapter in the broader context?",
            "How does this formula relate to real-world applications?",
            "Can you provide more examples of this literary device?",
            "Why is this historical event considered a turning point?",
            "How do these chemical reactions occur in nature?",
            "What are the implications of this economic theory?",
            "Can you explain this mathematical proof step by step?",
            "How does this biological process work in human cells?",
            "What are alternative interpretations of this passage?",
        ];

        // Sample AI responses
        $aiResponses = [
            "Gravity is the force that attracts objects with mass toward each other. On Earth, gravity pulls objects toward the center of the planet. This is why when you drop something, it falls to the ground. The more massive an object is, the stronger its gravitational pull. This is why the Sun's gravity keeps planets in orbit, as it is much more massive than Earth.",
            
            "This chapter establishes the foundation for the entire theoretical framework discussed in the book. It introduces key concepts that will be expanded upon in later sections and provides historical context for how these ideas developed. The author's approach here sets up the comparative analysis that forms the core of the subsequent chapters.",
            
            "This formula has numerous real-world applications. Engineers use it when designing structural supports for buildings to ensure they can withstand environmental forces. It's also applied in manufacturing processes to optimize material usage and in predictive algorithms for financial forecasting.",
            
            "Certainly! Additional examples include the extended metaphor in Shakespeare's Sonnet 18 where he compares a person to a summer's day, the vivid personification in Emily Dickinson's work where death is portrayed as a gentleman caller, and the dramatic irony in Oscar Wilde's 'The Importance of Being Earnest' where the audience knows information that characters don't.",
            
            "This event represented a paradigm shift in how societies approached governance. It challenged established hierarchies, introduced new philosophical ideas about individual rights, and created a template for future revolutionary movements. The aftereffects reshaped political boundaries and influenced policy development for generations.",
        ];

        // Sample highlight texts
        $highlightTexts = [
            "The force of gravity is proportional to the product of the masses and inversely proportional to the square of the distance between them.",
            "The molar mass of a compound is calculated by adding the atomic masses of all atoms in the molecular formula.",
            "Economic equilibrium occurs when supply equals demand in a competitive market.",
            "Photosynthesis is the process by which plants convert light energy into chemical energy.",
            "The fundamental theorem of calculus establishes the relationship between differentiation and integration.",
        ];

        // Create chat messages for each user
        foreach ($users as $user) {
            // Generate 0-5 chat messages per user
            $messageCount = rand(0, 5);
            // Only use ebooks with type 1 (PDF) or 2 (Video)
            $validEbooks = $ebooks->whereIn('e_book_type_id', [1, 2]);
            if ($validEbooks->isEmpty()) continue;
            $userEbooks = $validEbooks->random(min($messageCount, $validEbooks->count()));
            foreach ($userEbooks as $ebook) {
                $isPdf = $ebook->e_book_type_id == 1;
                $isVideo = $ebook->e_book_type_id == 2;
                $data = [
                    'user_id' => $user->id,
                    'e_book_id' => $ebook->id,
                    'question' => $questions[array_rand($questions)],
                    'ai_response' => $aiResponses[array_rand($aiResponses)],
                    'is_anonymous' => rand(0, 1) == 1,
                ];
                if ($isPdf) {
                    // PDF: page_number and highlight_text only
                    $data['page_number'] = rand(1, 100);
                    $includeHighlight = (rand(1, 10) <= 3);
                    $data['highlight_text'] = $includeHighlight ? $highlightTexts[array_rand($highlightTexts)] : null;
                    $data['sent_at'] = null;
                } elseif ($isVideo) {
                    // Video: sent_at only
                    $data['sent_at'] = now();
                    $data['page_number'] = null;
                    $data['highlight_text'] = null;
                }
                ChatMessage::create($data);
            }
        }
        
        $this->command->info('Chat messages seeded successfully.');
    }
}
