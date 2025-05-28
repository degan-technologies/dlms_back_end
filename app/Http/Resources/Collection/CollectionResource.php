<?php

namespace App\Http\Resources\Collection;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\EBook\EBookResource;

class CollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),            // Always include ebook counts (will be 0 if not loaded)
            'ebooks_count' => [
                'total' => $this->ebooks_count ?? 0,
                'downloadable' => $this->downloadable_ebooks_count ?? 0,
                'by_type' => [
                    'pdf' => $this->pdf_ebooks_count ?? 0,
                    'video' => $this->video_ebooks_count ?? 0
                ]
            ],
            
            // Include relationships when loaded
            'user' => $this->whenLoaded('user', function() {
                return [
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                    'email' => $this->user->email,
                ];
            }),
            'ebooks' => $this->whenLoaded('ebooks', function() {
                return EBookResource::collection($this->ebooks);
            }),
        ];
    }
}
