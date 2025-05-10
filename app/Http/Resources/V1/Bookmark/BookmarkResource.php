<?php

namespace App\Http\Resources\V1\Bookmark;

use Illuminate\Http\Resources\Json\JsonResource;

class BookmarkResource extends JsonResource
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
            'bookmarkable_id' => $this->bookmarkable_id,
            'bookmarkable_type' => $this->bookmarkable_type,
            'page_number' => $this->page_number,
            'position' => $this->position,
            'title' => $this->title,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'bookmarkable' => $this->whenLoaded('bookmarkable', function() {
                if ($this->bookmarkable_type === 'App\Models\EBook') {
                    return [
                        'id' => $this->bookmarkable->book_item_id,
                        'file_url' => $this->bookmarkable->file_url,
                        'file_format' => $this->bookmarkable->file_format,
                        'pages' => $this->bookmarkable->pages,
                        'book_item' => $this->bookmarkable->bookItem ? [
                            'id' => $this->bookmarkable->bookItem->id,
                            'title' => $this->bookmarkable->bookItem->title,
                            'author' => $this->bookmarkable->bookItem->author,
                            'cover_image_url' => $this->bookmarkable->bookItem->cover_image_url,
                        ] : null,
                    ];
                } elseif ($this->bookmarkable_type === 'App\Models\OtherAsset') {
                    return [
                        'id' => $this->bookmarkable->book_item_id,
                        'asset_type' => $this->bookmarkable->asset_type,
                        'media_type' => $this->bookmarkable->media_type,
                        'book_item' => $this->bookmarkable->bookItem ? [
                            'id' => $this->bookmarkable->bookItem->id,
                            'title' => $this->bookmarkable->bookItem->title,
                            'author' => $this->bookmarkable->bookItem->author,
                            'cover_image_url' => $this->bookmarkable->bookItem->cover_image_url,
                        ] : null,
                    ];
                }
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
