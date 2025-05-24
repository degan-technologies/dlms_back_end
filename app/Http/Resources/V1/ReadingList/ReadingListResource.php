<?php

namespace App\Http\Resources\V1\ReadingList;

use App\Http\Resources\V1\BookItem\BookItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ReadingListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'is_public' => $this->is_public,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'book_items' => $this->whenLoaded('bookItems', function() {
                return $this->bookItems->map(function($bookItem) {
                    return [
                        'id' => $bookItem->id,
                        'title' => $bookItem->title,
                        'author' => $bookItem->author,
                        'isbn' => $bookItem->isbn,
                        'cover_image_url' => $bookItem->cover_image_url,
                        'added_at' => $bookItem->pivot->added_at,
                        'notes' => $bookItem->pivot->notes,
                    ];
                });
            }),
            'user' => $this->whenLoaded('user', function() {
                return [
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                ];
            }),
        ];
    }
}
