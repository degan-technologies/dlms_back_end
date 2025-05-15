<?php

namespace App\Http\Resources\V1\BookItem;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BookItemCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->transform(function ($item) {
                // Helper function to process relations and remove timestamps
                $processRelation = function ($relation) {
                    if (!$relation) return null;
                    return collect($relation)->except(['created_at', 'updated_at', 'deleted_at'])->toArray();
                };
                
                $shelfData = null;
                if ($item->shelf) {
                    $shelfData = $processRelation($item->shelf);
                    $shelfData['section'] = $processRelation($item->shelf->section);
                }
                $libraryData = null;
                if ($item->library) {
                    $libraryData = $processRelation($item->library);
                    $libraryData['library_branch'] = $processRelation($item->library->libraryBranch);
                }
                
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'isbn' => $item->isbn,
                    'item_type' => $item->item_type,
                    'availability_status' => $item->availability_status,
                    'author' => $item->author,
                    'publication_year' => $item->publication_year,
                    'description' => $item->description,
                    'cover_image_url' => $item->cover_image_url,
                    'language' => $item->language,
                    'library' => $libraryData,
                    'library_branch_id' => $item->library_branch_id,
                    'shelf' => $shelfData,
                    'category' => $processRelation($item->category),
                    'publisher' => $processRelation($item->publisher),
                    
                ];
            }),
            'meta' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage()
            ],
        ];
    }
}