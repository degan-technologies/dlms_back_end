<?php

namespace App\Http\Controllers\ChatMessage;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatMessage\StoreChatMessageRequest;
use App\Http\Requests\ChatMessage\UpdateChatMessageRequest;
use App\Http\Resources\ChatMessage\ChatMessageCollection;
use App\Http\Resources\ChatMessage\ChatMessageResource;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatMessageController extends Controller
{
    /**
     * Display a listing of the chat messages.
     */
    public function index(Request $request)
    {
        $query = ChatMessage::query();
        
        // Filter by ebook ID
        if ($request->has('e_book_id')) {
            $query->where('e_book_id', $request->e_book_id);
        }
        
        // Search in question content
        if ($request->has('question')) {
            $query->where('question', 'like', '%' . $request->question . '%');
        }

        // Search in AI response content
        if ($request->has('ai_response')) {
            $query->where('ai_response', 'like', '%' . $request->ai_response . '%');
        }

        // Filter by anonymous status
        if ($request->has('is_anonymous')) {
            $query->where('is_anonymous', $request->boolean('is_anonymous'));
        }
        
        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // For non-anonymous messages, only show the current user's messages or all anonymous messages
        if (!$request->has('show_all') || !$request->boolean('show_all')) {
            $query->where(function ($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('is_anonymous', true);
            });
        }
        
        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebook', 'user'];
            $query->with(array_intersect($relationships, $allowedRelations));
            
            // Special case for nested relationships
            if (in_array('ebook.bookItem', $relationships)) {
                $query->with('ebook.bookItem');
            }
        }
        
        // Sort by latest first by default, or allow custom sorting
        if ($request->has('sort_by') && in_array($request->sort_by, ['created_at', 'updated_at'])) {
            $direction = $request->has('sort_direction') && $request->sort_direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->latest();
        }
        
        // Paginate the results
        $perPage = $request->per_page ?? 15;
        $chatMessages = $query->paginate($perPage);
        
        return new ChatMessageCollection($chatMessages);
    }

    /**
     * Store a newly created chat message in storage.
     */
    public function store(StoreChatMessageRequest $request)
    {
        $validated = $request->validated();
        
        // Add the authenticated user's ID
        $validated['user_id'] = auth()->id();
        
        // Set default for is_anonymous if not provided
        if (!isset($validated['is_anonymous'])) {
            $validated['is_anonymous'] = false;
        }
        
        try {
            // Send the question to Gemini API
            $apiKey = env('GEMINI_API_KEY');
            
            if (!$apiKey) {
                throw new \Exception('Gemini API key not configured');
            }
            
            $endpoint = env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($endpoint . '?key=' . $apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $validated['question']
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ]);
            
            if ($response->successful()) {
                $aiResponse = $response->json();
                // Extract the AI's response text from the response structure
                $aiResponseText = $aiResponse['candidates'][0]['content']['parts'][0]['text'] ?? null;
                $validated['ai_response'] = $aiResponseText ?? 'Sorry, I could not generate a response at this time.';
            } else {
                Log::error('Gemini API error: ' . $response->body());
                $validated['ai_response'] = 'Sorry, I could not generate a response at this time due to a technical issue.';
            }
        } catch (\Exception $e) {
            Log::error('Error with Gemini API: ' . $e->getMessage());
            $validated['ai_response'] = 'Sorry, I could not generate a response at this time due to a technical issue.';
        }
        
        // Create the chat message
        $chatMessage = ChatMessage::create($validated);
        
        // Load relationships for the response
        $chatMessage->load('ebook.bookItem');
        
        return new ChatMessageResource($chatMessage);
    }

    /**
     * Display the specified chat message.
     */
    public function show(Request $request, ChatMessage $chatMessage)
    {
        // Check if the user can view this message
        if (!$chatMessage->is_anonymous && $chatMessage->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebook', 'user'];
            $chatMessage->load(array_intersect($relationships, $allowedRelations));
            
            // Special case for nested relationships
            if (in_array('ebook.bookItem', $relationships)) {
                $chatMessage->load('ebook.bookItem');
            }
        }
        
        return new ChatMessageResource($chatMessage);
    }

    /**
     * Update the specified chat message in storage.
     */
    public function update(UpdateChatMessageRequest $request, ChatMessage $chatMessage)
    {
        $validated = $request->validated();
        
        // If question is being updated, we need to get a new AI response
        if (isset($validated['question'])) {
            try {
                // Send the question to Gemini API
                $apiKey = env('GEMINI_API_KEY');
                if (!$apiKey) {
                    throw new \Exception('Gemini API key not configured');
                }
                $endpoint = env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($endpoint . '?key=' . $apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $validated['question']
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ]
                ]);
                
                if ($response->successful()) {
                    $aiResponse = $response->json();
                    // Extract the AI's response text from the response structure
                    $aiResponseText = $aiResponse['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    $validated['ai_response'] = $aiResponseText ?? 'Sorry, I could not generate a response at this time.';
                } else {
                    Log::error('Gemini API error: ' . $response->body());
                    $validated['ai_response'] = 'Sorry, I could not generate a response at this time due to a technical issue.';
                }
            } catch (\Exception $e) {
                Log::error('Error with Gemini API: ' . $e->getMessage());
                $validated['ai_response'] = 'Sorry, I could not generate a response at this time due to a technical issue.';
            }
        }
        
        $chatMessage->update($validated);
        
        // Load relationships for the response
        $chatMessage->load('ebook.bookItem');
        
        return new ChatMessageResource($chatMessage);
    }

    /**
     * Remove the specified chat message from storage.
     */
    public function destroy(ChatMessage $chatMessage)
    {
        // Check if the chat message belongs to the authenticated user
        if ($chatMessage->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $chatMessage->delete();
        
        return response()->json(['message' => 'Chat message deleted successfully']);
    }
}
