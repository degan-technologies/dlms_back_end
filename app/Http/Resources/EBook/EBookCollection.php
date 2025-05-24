<?php

namespace App\Http\Resources\EBook;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EBookCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'pagination' => [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'total_pages' => $this->resource->lastPage(),
                'next_page_url' => $this->resource->nextPageUrl(),
                'prev_page_url' => $this->resource->previousPageUrl(),
                'from' => $this->resource->firstItem(),
                'to' => $this->resource->lastItem(),
            ],
            'links' => [
                'self' => url()->current(),
            ],
        ];
    }
}
