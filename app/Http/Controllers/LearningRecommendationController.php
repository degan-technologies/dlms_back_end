<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EBook;
use App\Models\Note;
use App\Models\ChatMessage;
use App\Models\Bookmark;
use App\Models\RecentlyViewed;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\EBook\EBookResource;

class LearningRecommendationController extends Controller
{
    /**
     * Get personalized learning recommendations for home page
     * Returns 5 EBook suggestions based on user behavior and AI
     */
    public function getRecommendations(Request $request)
    {
        $user = $request->user();
        $limit = $request->get('limit', 5); // Default 5 for home page
        $useAI = env('GEMINI_API_KEY') !== null;

        try {
            // 1. Analyze comprehensive user behavior patterns
            $userPatterns = $this->analyzeUserBehavior($user->id);
            
            // 2. Get algorithm-based recommendations
            $algorithmRecommendations = $this->getAlgorithmRecommendations($user->id, $userPatterns, $limit);
            
            // 3. Get AI-enhanced suggestions if API available
            $aiSuggestions = [];
            if ($useAI && !empty($userPatterns['learning_context'])) {
                $aiSuggestions = $this->getAISuggestions($userPatterns, $limit);
            }
            
            // 4. Combine and diversify results
            $recommendations = $this->combineRecommendations(
                $algorithmRecommendations, 
                $aiSuggestions, 
                $limit
            );

            return response()->json([
                'success' => true,
                'recommendations' => EBookResource::collection($recommendations),
                'user_insights' => [
                    'engagement_level' => $userPatterns['engagement_score'],
                    'learning_style' => $this->determineLearningStyle($userPatterns),
                    'progress_summary' => $this->getProgressSummary($userPatterns)
                ],
                'ai_powered' => $useAI,
                'total_suggestions' => count($recommendations),
                'algorithm_used' => $useAI ? 'hybrid_ai_algorithm' : 'behavioral_algorithm'
            ]);

        } catch (\Exception $e) {
            // Fallback to basic recommendations
            $fallbackRecs = $this->getFallbackRecommendations($user->id, $limit);
            
            return response()->json([
                'success' => true,
                'recommendations' => EBookResource::collection($fallbackRecs),
                'fallback_mode' => true,
                'message' => 'Using basic recommendations due to system limitations'
            ]);
        }
    }

    private function analyzeUserBehavior($userId)
    {
        // 1. Analyze EBook interactions through relationships
        $ebookInteractions = EBook::whereHas('bookmarks', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->orWhereHas('notes', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->orWhereHas('chatMessages', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with([
            'bookItem.category', 
            'bookItem.subject', 
            'bookItem.language', 
            'bookItem.grade',
            'bookmarks',
            'notes',
            'chatMessages',
            'collections',
            'ebookType'
        ])->withCount(['bookmarks', 'notes', 'chatMessages', 'collections'])
        ->get();

        // 2. Get recently viewed books for context
        $recentlyViewed = RecentlyViewed::where('user_id', $userId)
            ->with([
                'ebook.bookItem.category',
                'ebook.bookItem.subject',
                'ebook.bookItem.language',
                'ebook.bookItem.grade',
                'ebook.bookmarks',
                'ebook.notes',
                'ebook.chatMessages',
                'ebook.collections',
                'ebook.ebookType'
            ])
            ->orderBy('last_viewed_at', 'desc')
            ->take(10)
            ->get();

        // 3. Extract user preferences from all interactions
        $allBooks = $ebookInteractions->concat($recentlyViewed->pluck('ebook'))->unique('id');
        
        $categories = $allBooks->pluck('bookItem.category.id')->filter()->countBy()->sortDesc();
        $subjects = $allBooks->pluck('bookItem.subject.id')->filter()->countBy()->sortDesc();
        $languages = $allBooks->pluck('bookItem.language.id')->filter()->countBy()->sortDesc();
        $grades = $allBooks->pluck('bookItem.grade.id')->filter()->countBy()->sortDesc();

        // 4. Analyze detailed user engagement
        $noteStats = Note::where('user_id', $userId)
            ->select(
                DB::raw('COUNT(*) as total_notes'),
                DB::raw('AVG(page_number) as avg_page_depth'),
                DB::raw('COUNT(DISTINCT e_book_id) as books_with_notes'),
                DB::raw('AVG(LENGTH(content)) as avg_note_length')
            )->first();

        $chatStats = ChatMessage::where('user_id', $userId)
            ->select(
                DB::raw('COUNT(*) as total_questions'),
                DB::raw('COUNT(DISTINCT e_book_id) as books_questioned'),
                DB::raw('AVG(LENGTH(question)) as avg_question_length')
            )->first();

        $bookmarkStats = Bookmark::where('user_id', $userId)
            ->select(
                DB::raw('COUNT(*) as total_bookmarks'),
                DB::raw('COUNT(DISTINCT e_book_id) as books_bookmarked')
            )->first();

        // 5. Extract learning interests from content
        $interests = $this->extractUserInterests($userId);
        
        // 6. Calculate comprehensive engagement score
        $engagementScore = $this->calculateEngagementScore($noteStats, $chatStats, $bookmarkStats);

        return [
            'preferred_categories' => $categories->take(3)->keys()->toArray(),
            'preferred_subjects' => $subjects->take(3)->keys()->toArray(),
            'preferred_languages' => $languages->take(2)->keys()->toArray(),
            'preferred_grades' => $grades->take(2)->keys()->toArray(),
            'total_books_read' => $allBooks->count(),
            'engagement_score' => $engagementScore,
            'note_taking_habit' => $this->categorizeHabit($noteStats->total_notes ?? 0, 'notes'),
            'question_asking_habit' => $this->categorizeHabit($chatStats->total_questions ?? 0, 'questions'),
            'bookmark_habit' => $this->categorizeHabit($bookmarkStats->total_bookmarks ?? 0, 'bookmarks'),
            'interests' => $interests->take(5)->toArray(),
            'learning_context' => $this->buildLearningContext($noteStats, $chatStats, $interests),
            'reading_depth' => $this->analyzeReadingDepth($noteStats, $chatStats),
            'recent_focus' => $this->getRecentFocus($recentlyViewed)
        ];
    }

    private function getAlgorithmRecommendations($userId, $patterns, $limit)
    {
        $query = EBook::with([
            'bookItem.category',
            'bookItem.subject',
            'bookItem.language',
            'bookItem.grade',
            'bookmarks',
            'notes',
            'chatMessages',
            'collections',
            'ebookType'
        ])
        ->withCount(['bookmarks', 'notes', 'chatMessages', 'collections'])
        ->whereDoesntHave('bookmarks', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereDoesntHave('notes', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereNotIn('id', function($query) use ($userId) {
            $query->select('e_book_id')
                ->from('recently_viewed')
                ->where('user_id', $userId);
        });

        // Apply user preference filters with weighted scoring
        $scoringQuery = $query->select('e_books.*')
            ->selectRaw('0 as recommendation_score');

        // Category preference scoring (highest weight)
        if (!empty($patterns['preferred_categories'])) {
            $categoriesStr = implode(',', $patterns['preferred_categories']);
            $scoringQuery->selectRaw("
                CASE 
                    WHEN book_items.category_id IN ({$categoriesStr}) THEN 40
                    ELSE 0 
                END as category_score
            ");
        }

        // Subject preference scoring
        if (!empty($patterns['preferred_subjects'])) {
            $subjectsStr = implode(',', $patterns['preferred_subjects']);
            $scoringQuery->selectRaw("
                CASE 
                    WHEN book_items.subject_id IN ({$subjectsStr}) THEN 30
                    ELSE 0 
                END as subject_score
            ");
        }

        // Grade level preference
        if (!empty($patterns['preferred_grades'])) {
            $gradesStr = implode(',', $patterns['preferred_grades']);
            $scoringQuery->selectRaw("
                CASE 
                    WHEN book_items.grade_id IN ({$gradesStr}) THEN 20
                    ELSE 0 
                END as grade_score
            ");
        }

        // Language preference scoring
        if (!empty($patterns['preferred_languages'])) {
            $languagesStr = implode(',', $patterns['preferred_languages']);
            $scoringQuery->selectRaw("
                CASE 
                    WHEN book_items.language_id IN ({$languagesStr}) THEN 10
                    ELSE 0 
                END as language_score
            ");
        }

        $recommendations = $scoringQuery
            ->join('book_items', 'e_books.book_item_id', '=', 'book_items.id')
            ->withCount(['bookmarks', 'notes', 'chatMessages'])
            ->orderByRaw('(category_score + subject_score + grade_score + language_score + bookmarks_count + notes_count) DESC')
            ->orderBy('created_at', 'desc')
            ->limit($limit * 2) // Get more for diversity
            ->get();

        // Add diversity to recommendations
        return $this->diversifyRecommendations($recommendations, $limit);
    }

    private function getAISuggestions($patterns, $limit)
    {
        try {
            $apiKey = env('GEMINI_API_KEY');
            if (!$apiKey) return [];

            $prompt = $this->buildAdvancedAIPrompt($patterns);
            
            $response = Http::timeout(15)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post(env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent') . '?key=' . $apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.8,
                    'maxOutputTokens' => 800,
                    'topP' => 0.9,
                ]
            ]);

            if ($response->successful()) {
                $aiResponse = $response->json();
                $suggestions = $aiResponse['candidates'][0]['content']['parts'][0]['text'] ?? '';
                return $this->parseAdvancedAISuggestions($suggestions, $patterns, $limit);
            }

        } catch (\Exception $e) {
            Log::warning('AI suggestions failed: ' . $e->getMessage());
        }

        return [];
    }

    private function buildAdvancedAIPrompt($patterns)
    {
        $interests = implode(', ', $patterns['interests']);
        $recentFocus = implode(', ', $patterns['recent_focus']);
        
        return "As an educational AI assistant, analyze this student's learning profile and recommend specific ebook topics:

STUDENT PROFILE:
- Books engaged with: {$patterns['total_books_read']}
- Learning engagement level: {$patterns['engagement_score']}/10
- Key interests: {$interests}
- Recent focus areas: {$recentFocus}
- Learning style: {$patterns['note_taking_habit']} note-taker, {$patterns['question_asking_habit']} questioner
- Reading depth: {$patterns['reading_depth']}

TASK: Suggest 3-5 specific educational topics or subject areas that would:
1. Build on current interests while introducing new concepts
2. Match their learning engagement level
3. Fill potential knowledge gaps
4. Encourage continued learning progression

Format your response as a numbered list with brief explanations for each suggestion.";
    }

    private function parseAdvancedAISuggestions($aiText, $patterns, $limit)
    {
        $lines = explode("\n", $aiText);
        $topics = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^\d+\.?\s*(.+?)(?:\s*-\s*.*)?$/', $line, $matches) || 
                preg_match('/^[-*]\s*(.+?)(?:\s*-\s*.*)?$/', $line, $matches)) {
                $topic = trim($matches[1]);
                if (strlen($topic) > 3) {
                    $topics[] = $topic;
                }
            }
        }

        // Find books matching AI suggestions with user preferences
        $suggestions = collect();
        foreach (array_slice($topics, 0, $limit) as $topic) {
            $query = EBook::whereHas('bookItem', function($q) use ($topic) {
                $q->where('title', 'like', "%{$topic}%")
                  ->orWhere('description', 'like', "%{$topic}%")
                  ->orWhere('author', 'like', "%{$topic}%");
            })->with([
                'bookItem.category',
                'bookItem.subject',
                'bookItem.grade',
                'bookmarks',
                'notes',
                'chatMessages',
                'collections',
                'ebookType'
            ])->withCount(['bookmarks', 'notes', 'chatMessages', 'collections']);

            // Prefer books in user's preferred categories/subjects
            if (!empty($patterns['preferred_categories'])) {
                $query->whereHas('bookItem', function($q) use ($patterns) {
                    $q->whereIn('category_id', $patterns['preferred_categories']);
                });
            }

            $books = $query->limit(2)->get();
            $suggestions = $suggestions->merge($books);
        }

        return $suggestions->unique('id')->take($limit);
    }

    private function combineRecommendations($algorithmRecs, $aiSuggestions, $limit)
    {
        $combined = collect();
        
        // Interleave algorithm and AI suggestions for diversity
        $maxAlgorithm = ceil($limit * 0.6); // 60% algorithm
        $maxAI = $limit - $maxAlgorithm;     // 40% AI
        
        $combined = $combined->merge($algorithmRecs->take($maxAlgorithm));
        
        foreach ($aiSuggestions->take($maxAI) as $aiBook) {
            if (!$combined->contains('id', $aiBook->id)) {
                $combined->push($aiBook);
            }
        }

        // Fill remaining slots with algorithm suggestions if needed
        foreach ($algorithmRecs as $book) {
            if ($combined->count() >= $limit) break;
            if (!$combined->contains('id', $book->id)) {
                $combined->push($book);
            }
        }

        return $combined->take($limit);
    }

    private function getFallbackRecommendations($userId, $limit)
    {
        // Get popular books user hasn't interacted with
        return EBook::with([
            'bookItem.category',
            'bookItem.subject',
            'bookmarks',
            'notes',
            'chatMessages',
            'collections',
            'ebookType'
        ])
        ->withCount(['bookmarks', 'notes', 'chatMessages', 'collections'])
        ->whereDoesntHave('bookmarks', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereDoesntHave('notes', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->orderByDesc('bookmarks_count')
        ->orderByDesc('notes_count')
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get();
    }

    // Helper methods
    private function calculateEngagementScore($noteStats, $chatStats, $bookmarkStats)
    {
        $noteScore = min(4, ($noteStats->total_notes ?? 0) / 8);
        $chatScore = min(3, ($chatStats->total_questions ?? 0) / 5);
        $bookmarkScore = min(2, ($bookmarkStats->total_bookmarks ?? 0) / 10);
        $depthScore = min(1, ($noteStats->avg_page_depth ?? 0) / 100);
        
        return round($noteScore + $chatScore + $bookmarkScore + $depthScore, 1);
    }

    private function categorizeHabit($count, $type)
    {
        switch ($type) {
            case 'notes':
                if ($count >= 50) return 'heavy';
                if ($count >= 20) return 'active';
                if ($count >= 5) return 'moderate';
                return 'light';
            case 'questions':
                if ($count >= 20) return 'heavy';
                if ($count >= 10) return 'active';
                if ($count >= 3) return 'moderate';
                return 'light';
            case 'bookmarks':
                if ($count >= 30) return 'heavy';
                if ($count >= 15) return 'active';
                if ($count >= 5) return 'moderate';
                return 'light';
        }
        return 'unknown';
    }

    private function buildLearningContext($noteStats, $chatStats, $interests)
    {
        $context = [];
        
        if (($noteStats->total_notes ?? 0) > ($chatStats->total_questions ?? 0)) {
            $context[] = 'prefers independent study';
        } else {
            $context[] = 'learns through questioning';
        }
        
        if (($noteStats->avg_note_length ?? 0) > 100) {
            $context[] = 'detailed note-taker';
        }
        
        $context[] = 'interested in ' . implode(', ', $interests->take(3)->toArray());
        
        return implode(', ', $context);
    }

    private function analyzeReadingDepth($noteStats, $chatStats)
    {
        $avgDepth = $noteStats->avg_page_depth ?? 0;
        
        if ($avgDepth > 75) return 'deep reader';
        if ($avgDepth > 40) return 'thorough reader';
        if ($avgDepth > 15) return 'moderate reader';
        return 'surface reader';
    }

    private function getRecentFocus($recentlyViewed)
    {
        return $recentlyViewed->pluck('ebook.bookItem.category.category_name')
            ->filter()
            ->take(3)
            ->unique()
            ->toArray();
    }

    private function diversifyRecommendations($recommendations, $limit)
    {
        $diversified = collect();
        $usedCategories = [];
        
        // First pass: one book per category
        foreach ($recommendations as $book) {
            $categoryId = $book->bookItem->category_id ?? null;
            if ($categoryId && !in_array($categoryId, $usedCategories)) {
                $diversified->push($book);
                $usedCategories[] = $categoryId;
                if ($diversified->count() >= $limit) break;
            }
        }
        
        // Second pass: fill remaining slots
        foreach ($recommendations as $book) {
            if ($diversified->count() >= $limit) break;
            if (!$diversified->contains('id', $book->id)) {
                $diversified->push($book);
            }
        }
        
        return $diversified->take($limit);
    }

    private function extractUserInterests($userId)
    {
        $noteKeywords = Note::where('user_id', $userId)
            ->pluck('content')
            ->flatMap(function($content) {
                return $this->extractKeywords($content);
            });

        $questionKeywords = ChatMessage::where('user_id', $userId)
            ->pluck('question')
            ->flatMap(function($question) {
                return $this->extractKeywords($question);
            });

        return $noteKeywords->merge($questionKeywords)
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->keys();
    }

    private function extractKeywords($text)
    {
        $commonWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'what', 'how', 'why', 'when', 'where', 'this', 'that', 'these', 'those'];
        
        $words = str_word_count(strtolower($text), 1);
        return collect($words)
            ->filter(function($word) use ($commonWords) {
                return strlen($word) > 3 && !in_array($word, $commonWords);
            });
    }

    private function determineLearningStyle($patterns)
    {
        $noteHeavy = $patterns['note_taking_habit'] === 'heavy';
        $questionHeavy = $patterns['question_asking_habit'] === 'heavy';
        
        if ($noteHeavy && $questionHeavy) return 'interactive_learner';
        if ($noteHeavy) return 'analytical_learner';
        if ($questionHeavy) return 'inquisitive_learner';
        return 'casual_learner';
    }

    private function getProgressSummary($patterns)
    {
        return [
            'books_explored' => $patterns['total_books_read'],
            'engagement_trend' => $patterns['engagement_score'] > 6 ? 'highly_engaged' : 'moderately_engaged',
            'learning_consistency' => $this->assessConsistency($patterns)
        ];
    }

    private function assessConsistency($patterns)
    {
        $activities = [
            $patterns['note_taking_habit'],
            $patterns['question_asking_habit'], 
            $patterns['bookmark_habit']
        ];
        
        $activeCount = count(array_filter($activities, function($habit) {
            return in_array($habit, ['active', 'heavy']);
        }));
        
        if ($activeCount >= 2) return 'consistent';
        if ($activeCount >= 1) return 'moderate';
        return 'inconsistent';
    }
}