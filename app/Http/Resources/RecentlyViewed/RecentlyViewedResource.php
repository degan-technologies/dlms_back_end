<?php

namespace App\Http\Resources\RecentlyViewed;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\EBook\EBookResource;

class RecentlyViewedResource extends JsonResource
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
            'last_viewed_at' => $this->last_viewed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include relationships when they're loaded
            'ebook' => $this->whenLoaded('ebook', function() {
                return new EBookResource($this->ebook);
            }),
        ];
    }
}
