<?php

namespace App\Http\Resources\V1\BookItem;

use Illuminate\Http\Resources\Json\JsonResource;

class BookItemResource extends JsonResource
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
            'title' => $this->title,
            'isbn' => $this->isbn,
            'item_type' => $this->item_type,
            'availability_status' => $this->availability_status,
            'author' => $this->author,
            'publication_year' => $this->publication_year,
            'description' => $this->description,
            'cover_image_url' => $this->cover_image_url,
            'language' => $this->language,
            'library_branch_id' => $this->library_branch_id,
            'library_branch' => $this->whenLoaded('libraryBranch', function() {
                return [
                    'id' => $this->libraryBranch->id,
                    'name' => $this->libraryBranch->branch_name
                ];
            }),
            'shelf_id' => $this->shelf_id,
            'shelf' => $this->whenLoaded('shelf', function() {
                return [
                    'id' => $this->shelf->id,
                    'code' => $this->shelf->code,
                    'location' => $this->shelf->location
                ];
            }),
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function() {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->CategoryName
                ];
            }),
            'publisher_id' => $this->publisher_id,
            'publisher' => $this->whenLoaded('publisher', function() {
                return [
                    'id' => $this->publisher->PublisherID,
                    'name' => $this->publisher->PublisherName
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'specific_details' => $this->when($this->item_type, function() {
                switch ($this->item_type) {
                    case 'physical':
                        return $this->whenLoaded('book');
                    case 'ebook':
                        return $this->whenLoaded('ebook');
                    case 'other':
                        return $this->whenLoaded('otherAsset');
                    default:
                        return null;
                }
            })
        ];
    }
}