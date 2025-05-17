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
        $data = [
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
                
                
                // Process specific item details based on item type
                $specificDetails = null;
                if ($item->item_type === 'physical' && $item->book) {
                    $specificDetails = $processRelation($item->book);
                } elseif ($item->item_type === 'ebook' && $item->ebook) {
                    $specificDetails = $processRelation($item->ebook);
                } elseif ($item->item_type === 'other' && $item->otherAsset) {
                    $specificDetails = $processRelation($item->otherAsset);
                    // Include asset type information if available
                    if ($item->otherAsset->assetType) {
                        $specificDetails['asset_type'] = $processRelation($item->otherAsset->assetType);
                    }
                }
                
                // Get tags associated with the book item
                $tags = [];
                if ($item->tags) {
                    $tags = $item->tags->map(function ($tag) use ($processRelation) {
                        return $processRelation($tag);
                    })->toArray();
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
                    'is_new_arrival' => $item->is_new_arrival,
                    'language' => $item->language,
                    'library' => $libraryData,
                    'library_branch_id' => $item->library_branch_id,
                    'shelf' => $shelfData,
                    'category' => $processRelation($item->category),
                    'publisher' => $processRelation($item->publisher),
                    'grade' => $processRelation($item->grade),
                    'tags' => $tags,
                    'specific_details' => $specificDetails,
                    // Include the direct model references for complete access
                    'book' => $item->item_type === 'physical' ? $processRelation($item->book) : null,
                    'ebook' => $item->item_type === 'ebook' ? $processRelation($item->ebook) : null,
                    'other_asset' => $item->item_type === 'other' ? $processRelation($item->otherAsset) : null,
                ];
            })
        ];
        
        // Add pagination meta data if the resource is paginated
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $data['meta'] = [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'total_pages' => $this->resource->lastPage()
            ];
        } else {
            // For normal collections, provide basic meta data
            $data['meta'] = [
                'total' => $this->collection->count(),
                'count' => $this->collection->count(),
            ];
        }
        
        return $data;
    }
}