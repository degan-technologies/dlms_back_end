<?php

namespace App\Http\Resources\Book;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BookItem\BookItemResource;

class BookResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'edition' => $this->edition,
            'isbn' => $this->isbn,
            'title' => $this->title,
            'pages' => $this->pages,
            'is_borrowable' => $this->is_borrowable,
            'book_item_id' => $this->book_item_id,
            'is_reserved'=> $this->is_reserved,
            'publication_year' => $this->publication_year,
            'shelf_id' => $this->shelf_id,
            'library_id' => $this->library_id,
            // Include relationships when loaded
            'book_item' => new BookItemResource($this->whenLoaded('bookItem')),
            'condition' => $this->whenLoaded('bookCondition', function () {
                return [
                    'id' => $this->bookCondition->id,
                    'condition' => $this->bookCondition->condition,
                    'note' => $this->bookCondition->note,
                ];
            }),
            'shelf' => $this->whenLoaded('shelf', function () {
                return [
                    'id' => $this->shelf->id,
                    'code' => $this->shelf->code,
                    'location' => $this->shelf->location,
                ];
            }),
            'library' => $this->whenLoaded('library', function () {
                return [
                    'id' => $this->library->id,
                    'name' => $this->library->name,
                ];
            }),
        ];
    }
}
