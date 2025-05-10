<?php

namespace App\Http\Resources\V1\Note;

use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
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
            'notable_id' => $this->notable_id,
            'notable_type' => $this->notable_type,
            'content' => $this->content,
            'page_number' => $this->page_number,
            'position' => $this->position,
            'highlight_text' => $this->highlight_text,
            'color' => $this->color,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'notable' => $this->whenLoaded('notable', function() {
                if ($this->notable_type === 'App\Models\EBook') {
                    return [
                        'id' => $this->notable->book_item_id,
                        'file_url' => $this->notable->file_url,
                        'file_format' => $this->notable->file_format,
                        'pages' => $this->notable->pages,
                        'book_item' => $this->notable->bookItem ? [
                            'id' => $this->notable->bookItem->id,
                            'title' => $this->notable->bookItem->title,
                            'author' => $this->notable->bookItem->author,
                            'cover_image_url' => $this->notable->bookItem->cover_image_url,
                        ] : null,
                    ];
                } elseif ($this->notable_type === 'App\Models\OtherAsset') {
                    return [
                        'id' => $this->notable->book_item_id,
                        'asset_type' => $this->notable->asset_type,
                        'media_type' => $this->notable->media_type,
                        'book_item' => $this->notable->bookItem ? [
                            'id' => $this->notable->bookItem->id,
                            'title' => $this->notable->bookItem->title,
                            'author' => $this->notable->bookItem->author,
                            'cover_image_url' => $this->notable->bookItem->cover_image_url,
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
