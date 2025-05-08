<?php

namespace App\Http\Resources\V1\EBook;

use App\Http\Resources\V1\BookItem\BookItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EBookResource extends JsonResource
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
            'book_item_id' => $this->book_item_id,
            'file_url' => $this->file_url,
            'file_format' => $this->file_format,
            'file_size_mb' => $this->file_size_mb,
            'pages' => $this->pages,
            'is_downloadable' => $this->is_downloadable,
            'requires_authentication' => $this->requires_authentication,
            'drm_type' => $this->drm_type,
            'access_expires_at' => $this->access_expires_at,
            'max_downloads' => $this->max_downloads,
            'reader_app' => $this->reader_app,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'book_item' => new BookItemResource($this->whenLoaded('bookItem')),
        ];
    }
}