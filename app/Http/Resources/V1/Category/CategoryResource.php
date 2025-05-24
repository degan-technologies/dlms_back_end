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
            'name' => $this->category_name,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Include the parent category when loaded
            'parent' => $this->when(
                $this->relationLoaded('parent') && $this->parent,
                fn() => new CategoryResource($this->parent)
            ),

            // Include child categories when loaded
            'children' => $this->when(
                $this->relationLoaded('children') && $this->children,
                fn() => CategoryResource::collection($this->children)
            ),

            // Include book items when loaded
            'book_items' => $this->when(
                $this->relationLoaded('bookItems') && $this->bookItems,
                fn() => new BookItemCollection($this->bookItems)
            ),

        ];
    }
}
