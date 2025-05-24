<?php

namespace App\Http\Resources\V1\BookItem;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\Note\NoteResource;
use App\Http\Resources\V1\Bookmark\BookmarkResource;
use App\Http\Resources\V1\ChatMessage\ChatMessageResource;

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
            // Basic book item information
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'item_type' => $this->item_type,
            'availability_status' => $this->availability_status,
            'author' => $this->author,
            'publication_year' => $this->publication_year,
            'description' => $this->description,
            'cover_image_url' => $this->cover_image_url,
            'is_new_arrival' => $this->is_new_arrival,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Related entity IDs (for quick reference)
            'library_id' => $this->library_id,
            'shelf_id' => $this->shelf_id,
            'category_id' => $this->category_id,
           'category_name' => optional($this->category)->category_name,

            'publisher_id' => $this->publisher_id,
            'grade_id' => $this->grade_id,
            'language_id' => $this->language_id,
            
            // Related entities (when loaded)
            'library' => $this->whenLoaded('library', function() {
                return [
                    'id' => $this->library->id,
                    'name' => $this->library->name
                ];
            }),
            
            'shelf' => $this->whenLoaded('shelf', function() {
                return [
                    'id' => $this->shelf->id,
                    'code' => $this->shelf->code,
                    'location' => $this->shelf->location
                ];
            }),
            
            // 'category' => $this->whenLoaded('category', function() {
            //     return [
            //         'id' => $this->category->id,
            //         'name' => $this->category->category_name,
            //     ];
            // }),
            
            'publisher' => $this->whenLoaded('publisher', function() {
                return [
                    'id' => $this->publisher->id,
                    'name' => $this->publisher->name
                ];
            }),
            
            'grade' => $this->whenLoaded('grade', function() {
                return [
                    'id' => $this->grade->id,
                    'name' => $this->grade->name,
                    'level' => $this->grade->level
                ];
            }),
            
            'language' => $this->whenLoaded('language', function() {
                return [
                    'id' => $this->language->id,
                    'name' => $this->language->name,
                    'code' => $this->language->code
                ];
            }),
            
            'tags' => $this->whenLoaded('tags', function() {
                return $this->tags->map(function($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'slug' => $tag->slug
                    ];
                });
            }),
            
            // Type-specific details
            'item_details' => $this->getItemTypeDetails(),
            
            // User interaction data
            'notes' => $this->whenLoaded('notes', function() {
                return NoteResource::collection($this->notes);
            }),
            
            'bookmarks' => $this->whenLoaded('bookmarks', function() {
                return BookmarkResource::collection($this->bookmarks);
            }),
            
            'chat_messages' => $this->whenLoaded('chatMessages', function() {
                return ChatMessageResource::collection($this->chatMessages);
            })
        ];
    }
    
    /**
     * Get details specific to the item type (book, ebook, or other asset).
     *
     * @return array|null
     */
    protected function getItemTypeDetails()
    {
        switch ($this->item_type) {
            case 'physical':
                return $this->whenLoaded('book', function() {
                    return [
                        'edition' => $this->book->edition,
                        'pages' => $this->book->pages,
                        'cover_type' => $this->book->cover_type,
                        'dimensions' => $this->book->dimensions,
                        'weight_grams' => $this->book->weight_grams,
                        'barcode' => $this->book->barcode,
                        'shelf_location_detail' => $this->book->shelf_location_detail,
                        'reference_only' => $this->book->reference_only,
                    ];
                });
                
            case 'ebook':
                return $this->whenLoaded('ebook', function() {
                    $details = [
                        'file_path' => $this->ebook->file_path,
                        'file_format' => $this->ebook->file_format,
                        'file_size_mb' => $this->ebook->file_size_mb,
                        'pages' => $this->ebook->pages,
                        'is_downloadable' => $this->ebook->is_downloadable,
                    ];
                    
                    // Add polymorphic relationship data if loaded
                    if ($this->ebook->relationLoaded('notes')) {
                        $details['ebook_notes'] = NoteResource::collection($this->ebook->notes);
                    }
                    
                    if ($this->ebook->relationLoaded('bookmarks')) {
                        $details['ebook_bookmarks'] = BookmarkResource::collection($this->ebook->bookmarks);
                    }
                    
                    return $details;
                });
                
            case 'other':
                return $this->whenLoaded('otherAsset', function() {
                    $details = [
                        'media_type' => $this->otherAsset->media_type,
                        'file_path' => $this->otherAsset->file_path,
                        'embed_url' => $this->otherAsset->embed_url,
                        'duration_minutes' => $this->otherAsset->duration_minutes,
                        'is_downloadable' => $this->otherAsset->is_downloadable,
                        'is_available' => $this->otherAsset->is_available,
                        'location_notes' => $this->otherAsset->location_notes,
                    ];
                    
                    // Add asset type if loaded
                    if ($this->otherAsset->relationLoaded('assetType')) {
                        $details['asset_type'] = [
                            'id' => $this->otherAsset->assetType->id,
                            'name' => $this->otherAsset->assetType->name,
                            'description' => $this->otherAsset->assetType->description,
                            'is_electronic' => $this->otherAsset->assetType->is_electronic,
                        ];
                    }
                    
                    // Add polymorphic relationship data if loaded
                    if ($this->otherAsset->relationLoaded('notes')) {
                        $details['asset_notes'] = NoteResource::collection($this->otherAsset->notes);
                    }
                    
                    if ($this->otherAsset->relationLoaded('bookmarks')) {
                        $details['asset_bookmarks'] = BookmarkResource::collection($this->otherAsset->bookmarks);
                    }
                    
                    return $details;
                });
                
            default:
                return null;
        }
    }
}