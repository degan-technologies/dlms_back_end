<?php

namespace App\Http\Resources\EBook;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EBookResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */      public function toArray(Request $request): array {
        $userId = $request->user() ? $request->user()->id : null;
        return [
            'id' => $this->id,
            'book_item_id' => $this->book_item_id,
            'user_id' => $this->user_id,
            'file_name' => $this->file_name,
            'file_path' => $this->ebookType && $this->ebookType->name === 'PDF'
                ? url('api/ebooks/pdf/' . basename($this->file_path))
                : $this->file_path,
            
            'file_size_mb' => $this->file_size_mb,
            'pages' => $this->pages,
            'is_downloadable' => $this->is_downloadable,
            'e_book_type' => [
                'id' => $this->ebookType ? $this->ebookType->id : null,
                'name' => $this->ebookType ? $this->ebookType->name : null,
            ],
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            
            // Always include interaction counts
            'interactions' => [
                'bookmarks_count' => $this->bookmarks_count ?? $this->bookmarks()->count(),
                'notes_count' => $this->notes_count ?? $this->notes()->count(),
                'chat_messages_count' => $this->chat_messages_count ?? $this->chatMessages()->count(),
                'collections_count' => $this->collections_count ?? $this->collections()->count(),
            ],
            
            // Include user-specific bookmark if user is authenticated
            'user_bookmark' => $this->whenLoaded('bookmark', function() {
                return $this->bookmark ? [
                    'id' => $this->bookmark->id,
                    'title' => $this->bookmark->title,
                    'created_at' => $this->bookmark->created_at->format('Y-m-d H:i:s'),
                ] : null;
            }),
              // Include relationships when loaded
            'bookmarks' => $this->whenLoaded('bookmarks', function() {
                return $this->bookmarks->count() > 0 ? $this->bookmarks->map(function($bookmark) {
                    return [
                        'id' => $bookmark->id,
                        'user_id' => $bookmark->user_id,
                        'title' => $bookmark->title,
                        'created_at' => $bookmark->created_at->format('Y-m-d H:i:s'),
                    ];
                }) : null;
            }),
            
            'notes' => $this->whenLoaded('notes', function() {
                return $this->notes->count() > 0 ? $this->notes->map(function($note) {
                    return [
                        'id' => $note->id,
                        'user_id' => $note->user_id,
                        'content' => $note->content,
                        'page_number' => $note->page_number,
                        'highlight_text' => $note->highlight_text,
                        'sent_at' => $note->sent_at ? $note->sent_at->format('Y-m-d H:i:s') : null,
                        'created_at' => $note->created_at->format('Y-m-d H:i:s'),
                    ];
                }) : null;
            }),
            
            'chat_messages' => $this->whenLoaded('chatMessages', function() {
                return $this->chatMessages->count() > 0 ? $this->chatMessages->map(function($message) {
                    return [
                        'id' => $message->id,
                        'user_id' => $message->user_id,
                        'question' => $message->question,
                        'ai_response' => $message->ai_response,
                        'page_number' => $message->page_number,
                        'highlight_text' => $message->highlight_text,
                        'is_anonymous' => $message->is_anonymous,
                        'sent_at' => $message->sent_at ? $message->sent_at->format('Y-m-d H:i:s') : null,
                        'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    ];
                }) : null;
            }),
            
            'collections' => $this->whenLoaded('collections', function() {
                return $this->collections->count() > 0 ? $this->collections->map(function($collection) {
                    return [
                        'id' => $collection->id,
                        'name' => $collection->name,
                        'user_id' => $collection->user_id,
                        'created_at' => $collection->created_at->format('Y-m-d H:i:s'),
                    ];
                }) : null;
            }),
        ];
    }
}
