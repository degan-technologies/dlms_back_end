<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OtherAssetResource extends JsonResource
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
            'id' => $this->book_item_id,
            'book_item_id' => $this->book_item_id,
            'asset_type_id' => $this->asset_type_id,
            'asset_type_name' => $this->whenLoaded('assetType', function() {
                return $this->assetType->name;
            }),
            'media_type' => $this->media_type,
            'unique_id' => $this->unique_id,
            'duration_minutes' => $this->duration_minutes,
            'manufacturer' => $this->manufacturer,
            'physical_condition' => $this->physical_condition,
            'location_details' => $this->location_details,
            'acquisition_date' => $this->acquisition_date,
            'usage_instructions' => $this->usage_instructions,
            'restricted_access' => $this->restricted_access,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'title' => $this->whenLoaded('bookItem', function() {
                return $this->bookItem->title;
            }),
            'description' => $this->whenLoaded('bookItem', function() {
                return $this->bookItem->description;
            }),
            'availability_status' => $this->whenLoaded('bookItem', function() {
                return $this->bookItem->availability_status;
            }),
            'library_branch' => $this->whenLoaded('libraryBranch', function() {
                return [
                    'id' => $this->libraryBranch->id,
                    'name' => $this->libraryBranch->branch_name
                ];
            }),
            'shelf' => $this->whenLoaded('shelf', function() {
                return [
                    'id' => $this->shelf->id,
                    'code' => $this->shelf->code,
                    'location' => $this->shelf->location
                ];
            }),
            'category' => $this->whenLoaded('category', function() {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->category_name
                ];
            }),
        ];
    }
}