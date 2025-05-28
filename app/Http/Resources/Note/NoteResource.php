<?php

namespace App\Http\Resources\Note;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\EBook\EBookResource;

class NoteResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id ?? null,
            'user_id' => $this->user_id ?? null,
            'e_book_id' => $this->e_book_id ?? null,
            'content' => $this->content ?? null,
            'page_number' => $this->page_number ?? null,
            'highlight_text' => $this->highlight_text ?? null,
            'sent_at' => $this->sent_at ? $this->sent_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            // Include relationships when loaded
            'user' => $this->whenLoaded('user', function () {
            return [
                'id' => $this->user->id ?? null,
                'username' => $this->user->username ?? null,
                'email' => $this->user->email ?? null,
            ];
            }),
            'ebook' => $this->whenLoaded('ebook', function () {
            return $this->ebook ? new EBookResource($this->ebook) : null;
            }),
        ];
    }
}
