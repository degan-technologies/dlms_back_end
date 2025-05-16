<?php

namespace App\Http\Resources\Constant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LibraryCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = LibraryResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection
        ];
    }
}
