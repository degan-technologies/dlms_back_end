<?php

namespace App\Http\Resources\Book;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BookResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'book_item_id' => $this->book_item_id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'cover_image' => $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null,
            'edition' => $this->edition,
            'pages' => $this->pages,
            'is_borrowable' => $this->is_borrowable,
            'is_reserved' => $this->is_reserved,
            'library' => [
                'id' => $this->library?->id,
                'name' => $this->library?->name,
            ],
            'shelf' => [
                'id' => $this->shelf?->id,
                'name' => $this->shelf?->code,
            ],
            'publication_year' => $this->publication_year,
        ];
    }
}
