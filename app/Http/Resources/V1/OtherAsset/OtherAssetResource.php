<?php

namespace App\Http\Resources\V1\OtherAsset;

use App\Http\Resources\V1\AssetType\AssetTypeResource;
use App\Http\Resources\V1\BookItem\BookItemResource;
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
            'book_item_id' => $this->book_item_id,
            'asset_type' => $this->asset_type,
            'asset_type_id' => $this->asset_type_id,
            'media_type' => $this->media_type,
            'unique_id' => $this->unique_id,
            'duration_minutes' => $this->duration_minutes,
            'formatted_duration' => $this->getFormattedDuration(),
            'manufacturer' => $this->manufacturer,
            'physical_condition' => $this->physical_condition,
            'location_details' => $this->location_details,
            'acquisition_date' => $this->acquisition_date,
            'usage_instructions' => $this->usage_instructions,
            'restricted_access' => $this->restricted_access,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'book_item' => new BookItemResource($this->whenLoaded('bookItem')),
            'asset_type_details' => new AssetTypeResource($this->whenLoaded('assetType')),
        ];
    }
}