<?php

namespace App\Http\Resources\V1\Language;

use Illuminate\Http\Resources\Json\JsonResource;

class LanguageResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'is_active' => (bool)$this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Only include book items count when specifically requested
            'book_items_count' => $this->when($request->has('include_counts'), 
                function() {
                    return $this->bookItems()->count();
                }
            ),
            // Include related book items only when specifically requested
            'book_items' => $this->when($request->has('include_book_items'), 
                function() {
                    return $this->bookItems;
                }
            ),
        ];
    }
}
