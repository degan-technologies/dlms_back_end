<?php

namespace App\Http\Resources\V1\RecentlyViewed;

use Illuminate\Http\Resources\Json\JsonResource;

class RecentlyViewedResource extends JsonResource
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
            'book_item_id' => $this->book_item_id,
            'last_viewed_at' => $this->last_viewed_at,
            'view_count' => $this->view_count,
            'last_page_viewed' => $this->last_page_viewed,
            'view_duration' => $this->view_duration,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'book_item' => $this->whenLoaded('bookItem', function() {
                return [
                    'id' => $this->bookItem->id,
                    'title' => $this->bookItem->title,
                    'author' => $this->bookItem->author,
                    'isbn' => $this->bookItem->isbn,
                    'item_type' => $this->bookItem->item_type,
                    'availability_status' => $this->bookItem->availability_status,
                    'cover_image_url' => $this->bookItem->cover_image_url,
                    'description' => $this->bookItem->description,
                ];
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
