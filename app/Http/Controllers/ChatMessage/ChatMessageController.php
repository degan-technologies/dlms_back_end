<?php

namespace App\Http\Controllers\ChatMessage;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatMessage\StoreChatMessageRequest;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\ChatMessage\ChatMessageResource;
use App\Http\Resources\ChatMessage\ChatMessageCollection;
use Illuminate\Support\Facades\Http;

class ChatMessageController extends Controller {
    /**
     * Display a listing of the chat messages.
     */
    public function index(Request $request) {
        $query = ChatMessage::query();

        // Only show the current user's messages
        $query->where('user_id', $request->user()->id);

        // Filter by ebook ID
        if ($request->has('e_book_id')) {
            $query->where('e_book_id', $request->e_book_id);
        }

        // Filter by page number
        if ($request->has('page_number')) {
            $query->where('page_number', $request->page_number);
        }

        // Filter by anonymous status
        if ($request->has('is_anonymous')) {
            $query->where('is_anonymous', $request->boolean('is_anonymous'));
        }

        // Search in question, highlight_text, ai_response
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('question', 'like', "%$searchTerm%")
                    ->orWhere('highlight_text', 'like', "%$searchTerm%")
                    ->orWhere('ai_response', 'like', "%$searchTerm%");
            });
        }

        // Include relationships if requested
        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebook', 'user'];
            $query->with(array_intersect($relationships, $allowedRelations));
        }

        // Custom sorting
        if ($request->has('sort_by') && in_array($request->sort_by, ['created_at', 'updated_at', 'page_number'])) {
            $direction = $request->has('sort_direction') && $request->sort_direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->latest();
        }

        // Paginate the results
        $perPage = $request->per_page ?? 15;
        $messages = $query->paginate($perPage);

        return new ChatMessageCollection($messages);
    }

    /**
     * Store a newly created chat message in storage.
     */
    public function store(StoreChatMessageRequest $request) {
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

                $validated['ai_response'] = 'Sorry, I could not generate a response at this time due to a technical issue.';
            }
        } catch (\Exception $e) {

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
    public function show(Request $request, ChatMessage $chatMessage) {
        // Check if the user can view this message
        if ($chatMessage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        if ($request->has('with')) {
            $relationships = explode(',', $request->with);
            $allowedRelations = ['ebook', 'user'];
            $chatMessage->load(array_intersect($relationships, $allowedRelations));
        }

        return new ChatMessageResource($chatMessage);
    }

    /**
     * Update the specified chat message in storage.
     */
    public function update(Request $request, ChatMessage $chatMessage) {
        // Check if the user can update this message
        if ($chatMessage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'question' => 'sometimes|required|string',
            'highlight_text' => 'nullable|string',
            'ai_response' => 'nullable|string',
            'page_number' => 'nullable|integer',
            'minute' => 'nullable|integer',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $chatMessage->update($validated);

        $chatMessage->load(['ebook', 'user']);

        return new ChatMessageResource($chatMessage);
    }

    /**
     * Remove the specified chat message from storage.
     */
    public function destroy(Request $request, ChatMessage $chatMessage) {
        // Check if the chat message belongs to the authenticated user
        if ($chatMessage->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $chatMessage->delete();

        return response()->json(['message' => 'Chat message deleted successfully']);
    }
}
