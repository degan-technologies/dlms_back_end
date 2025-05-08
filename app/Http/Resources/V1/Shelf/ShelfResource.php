<?php

namespace App\Http\Resources\V1\Shelf;

use Illuminate\Http\Resources\Json\JsonResource;

class ShelfResource extends JsonResource
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
            'code' => $this->code,
            'location' => $this->location,
            'capacity' => $this->capacity,
            'is_active' => $this->is_active,
            'section_id' => $this->section_id,
            'library_branch_id' => $this->library_branch_id,
            'occupancy' => $this->occupancy,
            'remaining_capacity' => $this->remaining_capacity,
            'full_location' => $this->full_location,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'section' => $this->whenLoaded('section', function() {
                return [
                    'id' => $this->section->id,
                    'name' => $this->section->name
                ];
            }),
            'library_branch' => $this->whenLoaded('libraryBranch', function() {
                return [
                    'id' => $this->libraryBranch->id,
                    'name' => $this->libraryBranch->branch_name
                ];
            }),
        ];
    }
}