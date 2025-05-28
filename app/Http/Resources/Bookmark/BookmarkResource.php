<?php

namespace App\Http\Resources\Bookmark;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\EBook\EBookResource;

class BookmarkResource extends JsonResource
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
            'user_id' => $this->user_id,
            'e_book_id' => $this->e_book_id,
            'title' => $this->title,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            // Include the ebook details when loaded
            'ebook' => $this->whenLoaded('ebook', function() {
                return [
                    'id' => $this->ebook->id,
                    'title' => $this->ebook->bookItem->title ?? null,
                    'author' => $this->ebook->bookItem->author ?? null,
                    'cover_image_url' => $this->ebook->bookItem->cover_image_url ?? null,
                    'file_format' => $this->ebook->file_format,
                    'is_downloadable' => $this->ebook->is_downloadable,
                    'ebook_type' => $this->ebook->relationLoaded('ebookType') && $this->ebook->ebookType ? [
                        'id' => $this->ebook->ebookType->id,
                        'name' => $this->ebook->ebookType->name,
                    ] : null,
                ];
            }),
            // Include user details when loaded
            'user' => $this->whenLoaded('user', function() {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'username' => $this->user->username,
                ];
            }),
        ];
    }
}
