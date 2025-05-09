<?php

namespace App\Http\Resources\V1\AssetType;

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
            'requires_special_handling' => $this->requires_special_handling,
            'is_electronic' => $this->is_electronic,
            'icon' => $this->icon,
            'metadata' => $this->metadata,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}