<?php

namespace App\Http\Resources\Constant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // 
        return [
            'id' => $this->id,
            'name' => $this->category_name,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
            'books_count' => $this->books_count ?? 0,
            
        ];
    }
}
