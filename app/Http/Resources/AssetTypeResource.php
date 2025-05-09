<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssetTypeResource extends JsonResource
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
            'description' => $this->description,
            'is_electronic' => $this->is_electronic,
            'file_type_category' => $this->file_type_category,
            'allowed_extensions' => $this->allowed_extensions,
            'max_file_size' => $this->max_file_size,
            'icon' => $this->icon,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'other_assets_count' => $this->when($this->relationLoaded('otherAssets'), function() {
                return $this->otherAssets->count();
            }),
        ];
    }
}