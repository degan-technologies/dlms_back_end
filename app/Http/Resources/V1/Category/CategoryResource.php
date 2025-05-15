<?php

namespace App\Http\Resources\V1\Category;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\BookItem\BookItemCollection;

class CategoryResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'icon' => $this->icon,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Include the parent category when loaded
            'parent' => $this->when($this->relationLoaded('parent'), new CategoryResource($this->parent)),
            // Include child categories when loaded
            'children' => $this->when(
                $this->relationLoaded('children'), 
                CategoryResource::collection($this->children)
            ),
            // Include book items count when requested
            'book_items_count' => $this->when(
                $request->has('with_counts'), 
                function() {
                    return $this->bookItems()->count();
                }
            ),
            // Include book items when loaded
            'book_items' => $this->when(
                $this->relationLoaded('bookItems'), 
                new BookItemCollection($this->bookItems)
            ),
        ];
    }
}
