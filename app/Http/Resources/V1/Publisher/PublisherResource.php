<?php

namespace App\Http\Resources\V1\Publisher;

use Illuminate\Http\Resources\Json\JsonResource;

class PublisherResource extends JsonResource
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
            'id' => $this->PublisherID,
            'name' => $this->PublisherName,
            'address' => $this->Address,
            'contact_info' => $this->ContactInfo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}