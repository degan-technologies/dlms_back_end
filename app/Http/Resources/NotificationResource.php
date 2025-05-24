<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'user' => $this->user ? $this->user->only(['id', 'username', 'email']) : null,
            'type' => $this->type ? $this->type->type : null,
            // 'created_at' => $this->created_at,
        ];
    }
}
