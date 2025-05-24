<?php

namespace App\Http\Resources\EBook;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BookItem\BookItemResource;

class EBookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'book_item_id' => $this->book_item_id,
            'file_path' => $this->file_path,
            'file_format' => $this->file_format,
            'file_name' => $this->file_name,
            'isbn' => $this->isbn,            'file_size_mb' => $this->file_size_mb,
            'pages' => $this->pages,
            'is_downloadable' => $this->is_downloadable,
            'e_book_type_id' => $this->e_book_type_id,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            
            // Include relationships when loaded
            'book_item' => new BookItemResource($this->whenLoaded('bookItem')),
            'ebook_type' => $this->whenLoaded('ebookType', function() {
                return [
                    'id' => $this->ebookType->id,
                    'name' => $this->ebookType->name,
                ];
            }),
            'bookmarks' => $this->whenLoaded('bookmarks', function() {
                return $this->bookmarks->map(function($bookmark) {
                    return [                        'id' => $bookmark->id,
                        'user_id' => $bookmark->user_id,
                        'title' => $bookmark->title,
                        'created_at' => $bookmark->created_at ? $bookmark->created_at->format('Y-m-d H:i:s') : null,
                        'user' => $bookmark->user ? [
                            'id' => $bookmark->user->id,
                            'username' => $bookmark->user->username,
                        ] : null,
                    ];
                });
            }),
            'notes' => $this->whenLoaded('notes', function() {
                return $this->notes->map(function($note) {
                    return [
                        'id' => $note->id,
                        'user_id' => $note->user_id,
                        'content' => $note->content,
                        'page_number' => $note->page_number,
                        'highlight_text' => $note->highlight_text,
                        'metadata' => $note->metadata,
                        'created_at' => $note->created_at->format('Y-m-d H:i:s'),
                        'user' => $note->user ? [
                            'id' => $note->user->id,
                            'username' => $note->user->username,
                        ] : null,
                    ];
                });
            }),
            'chat_messages' => $this->whenLoaded('chatMessages', function() {
                return $this->chatMessages->map(function($message) {
                    return [
                        'id' => $message->id,
                        'user_id' => $message->user_id,
                        'question' => $message->question,
                        'ai_response' => $message->ai_response,
                        'is_anonymous' => $message->is_anonymous,
                        'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                        'user' => !$message->is_anonymous && $message->user ? [
                            'id' => $message->user->id,
                            'username' => $message->user->username,
                        ] : null,
                    ];
                });
            }),
            'collections' => $this->whenLoaded('collections', function() {
                return $this->collections->map(function($collection) {
                    return [
                        'id' => $collection->id,
                        'name' => $collection->name,
                        'user_id' => $collection->user_id,
                        'created_at' => $collection->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            }),
        ];
    }
}
