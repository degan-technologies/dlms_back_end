<?php

namespace App\Http\Resources\V1\Book;

use App\Http\Resources\V1\BookItem\BookItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
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
            'edition' => $this->edition,
            'pages' => $this->pages,
            'cover_type' => $this->cover_type,
            'dimensions' => $this->dimensions,
            'weight_grams' => $this->weight_grams,
            'barcode' => $this->barcode,
            'shelf_location_detail' => $this->shelf_location_detail,
            'reference_only' => $this->reference_only,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'book_item' => new BookItemResource($this->whenLoaded('bookItem')),
        ];
    }
}