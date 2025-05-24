<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ChatMessage\StoreChatMessageRequest;
use App\Http\Resources\V1\ChatMessage\ChatMessageCollection;
use App\Http\Resources\V1\ChatMessage\ChatMessageResource;
use App\Models\BookItem;
use App\Models\ChatMessage;
use Google\Client;
use Google\Service\Aiplatform;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ChatMessageController extends Controller
{
    /**
     * Display all chat messages for a book item.
     */
    public function index(Request $request, BookItem $bookItem)
    {
        $query = ChatMessage::query()->where('book_item_id', $bookItem->id);

        // Authorization check - users can only see their own messages unless they're admin
        if (!$request->user()->hasRole('admin')) {
            $query->where(function($q) use ($request) {
                $q->where('user_id', $request->user()->id)
                  ->orWhere('is_anonymous', true);
            });
        }

        $chatMessages = $query->latest()->paginate($request->per_page ?? 15);
        
        return new ChatMessageCollection($chatMessages);
    }

    /**
     * Store a newly created chat message using Gemini API.
     */
    public function store(StoreChatMessageRequest $request, BookItem $bookItem)
    {
        $validated = $request->validated();
        
        // Get book context for better AI responses
        $bookContext = "Title: {$bookItem->title}\n";
        $bookContext .= "Author: {$bookItem->author}\n";
        $bookContext .= "Description: {$bookItem->description}\n";
        
        // Get response from Gemini API
        $aiResponse = $this->getGeminiResponse($validated['question'], $bookContext);
        
        $chatMessage = ChatMessage::create([
            'book_item_id' => $bookItem->id,
            'user_id' => Auth::id(),
            'question' => $validated['question'],
            'ai_response' => $aiResponse,
            'is_anonymous' => $validated['is_anonymous'] ?? false,
        ]);
        
        return new ChatMessageResource($chatMessage);
    }

    /**
     * Display the specified chat message.
     */
    public function show(ChatMessage $chatMessage)
    {
        // Authorization check
        if (!Auth::user()->hasRole('admin') && 
            Auth::id() !== $chatMessage->user_id && 
            !$chatMessage->is_anonymous) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        return new ChatMessageResource($chatMessage);
    }

    /**
     * Remove the specified chat message from storage.
     */
    public function destroy(ChatMessage $chatMessage)
    {
        // Authorization check
        if (!Auth::user()->hasRole('admin') && Auth::id() !== $chatMessage->user_id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        $chatMessage->delete();
        
        return response()->json(['message' => 'Chat message deleted successfully']);
    }
      /**
     * Get response from Gemini API.
     */
    protected function getGeminiResponse(string $question, string $context)
    {
        $apiKey = config('services.gemini.api_key');
        $endpoint = config('services.gemini.endpoint');
        
        // Make sure we have both the API key and endpoint
        if (empty($apiKey) || empty($endpoint)) {
            \Log::error('Gemini API key or endpoint is not set');
            return 'Sorry, the AI service is not properly configured.';
        }
        
        try {
            // Adding the API key as a query parameter, as required by Google Gemini API
            $fullEndpoint = $endpoint . '?key=' . $apiKey;
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($fullEndpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "You are a helpful AI book assistant. Use the following context information about the book to answer the user's question.\n\nContext information: {$context}\n\nUser question: {$question}\n\nProvide a helpful, accurate, and concise response."],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE',
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE',
                    ],
                    [
                        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE',
                    ],
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE',
                    ],
                ],
            ]);
            
            // Log the response for debugging if needed
            if ($response->failed()) {
                \Log::error('Gemini API Error: ' . $response->body());
                return 'Sorry, I could not generate a response at this time.';
            }
            
            $responseData = $response->json();
            
            // Extract the response text from the API response
            return $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not generate a response at this time.';
        } catch (\Exception $e) {
            // Log the error and return a generic message
            \Log::error('Gemini API Error: ' . $e->getMessage());
            return 'Sorry, I could not generate a response at this time.';
        }
    }
}
