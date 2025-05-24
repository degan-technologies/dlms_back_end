<?php

namespace App\Http\Resources\EBook;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EBookResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */    
    public function toArray(Request $request): array {
        $userId = $request->user() ? $request->user()->id : null;
        return [
            'id' => $this->id,
            'book_item_id' => $this->book_item_id,
            'user_id' => $this->user_id,
            'file_name' => $this->file_name,
            'file_path' => $this->ebookType && $this->ebookType->name === 'PDF'
                ? url('api/ebooks/pdf/' . basename($this->file_path))
                : $this->file_path,
            
            'file_size_mb' => $this->file_size_mb,
            'pages' => $this->pages,
            'is_downloadable' => $this->is_downloadable,
            'e_book_type' => [
                'id' => $this->ebookType ? $this->ebookType->id : null,
                'name' => $this->ebookType ? $this->ebookType->name : null,
            ],
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
