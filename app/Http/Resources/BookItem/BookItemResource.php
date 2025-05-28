<?php

namespace App\Http\Resources\BookItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BookItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */    public function toArray(Request $request): array
    {
        // Common BookItem properties
        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'cover_image' => $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null,
            'grade' => [
                'id' => $this->grade_id,
                'name' => optional($this->grade)->name,
            ],
            'library' => [
                'id' => $this->library_id,
                'name' => optional($this->library)->name,
            ],
            'category' => [
                'id' => $this->category_id,
                'name' => optional($this->category)->category_name,
            ],
            'language' => [
                'id' => $this->language_id,
                'name' => optional($this->language)->name,
            ],
            'subject' => [
                'id' => $this->subject_id,
                'name' => optional($this->subject)->name,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        
        // Add book counts if they exist
        if (isset($this->books_count)) {
            $data['books_count'] = [
                'total' => $this->books_count,
                'available' => $this->available_books_count ?? 0
            ];
        }
        
        // Add ebook counts if they exist
        if (isset($this->ebooks_count)) {
            $data['ebooks_count'] = [
                'total' => $this->ebooks_count,
                'downloadable' => $this->downloadable_ebooks_count ?? 0,
                'by_type' => [
                    'pdf' => $this->pdf_ebooks_count ?? 0,
                    'audio' => $this->audio_ebooks_count ?? 0,
                    'video' => $this->video_ebooks_count ?? 0
                ]
            ];
        }
        
        // Add teacher information if it's loaded
        if ($this->relationLoaded('user') && $this->user && $this->user->relationLoaded('staff') && $this->user->staff) {
            $data['teacher'] = [
                'id' => $this->user->id,
                'first_name' => $this->user->staff->first_name,
                'last_name' => $this->user->staff->last_name,
                'department' => $this->user->staff->department
            ];
        }
        
        // Add books and ebooks data if they are loaded (for show method)
        if ($this->relationLoaded('books') && count($this->books) > 0) {
            $data['books'] = $this->books->map(function($book) {
                return [
                    'id' => $book->id,
                    'edition' => $book->edition,
                    'isbn' => $book->isbn,
                    'pages' => $book->pages,
                    'is_borrowable' => $book->is_borrowable,
                    'is_reserved' => $book->is_reserved,
                    'publication_year' => $book->publication_year,
                    'shelf_id' => $book->shelf_id,
                    'shelf_name' => optional($book->shelf)->name,
                    'library_id' => $book->library_id,
                    'library_name' => optional($book->library)->name,
                ];
            });
        }
        
        if ($this->relationLoaded('ebooks') && count($this->ebooks) > 0) {
            $data['ebooks'] = $this->ebooks->map(function($ebook) {
                return [
                    'id' => $ebook->id,
                    'file_name' => $ebook->file_name,
                    'file_path' => $ebook->file_path,
                    'file_format' => $ebook->file_format,
                    'file_size_mb' => $ebook->file_size_mb,
                    'pages' => $ebook->pages,
                    'is_downloadable' => $ebook->is_downloadable,
                    'type' => optional($ebook->ebookType)->name,
                    // Add bookmark, notes, and chat messages if they are loaded
                    'bookmark' => $ebook->relationLoaded('bookmark') ? $ebook->bookmark : null,
                    'notes_count' => $ebook->relationLoaded('notes') ? $ebook->notes->count() : null,
                    'chat_messages_count' => $ebook->relationLoaded('chatMessages') ? $ebook->chatMessages->count() : null,
                ];
            });
        }
        
        return $data;
    }
}
