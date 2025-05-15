<?php

namespace App\Http\Resources\V1\ChatMessage;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
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
            'book_item_id' => $this->book_item_id,
            'user_id' => $this->user_id,
            'question' => $this->question,
            'ai_response' => $this->ai_response,
            'is_anonymous' => $this->is_anonymous,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->when(!$this->is_anonymous && $this->user, function() {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'book_item' => $this->whenLoaded('bookItem', function() {
                return [
                    'id' => $this->bookItem->id,
                    'title' => $this->bookItem->title,
                ];
            }),
        ];
    }
}
