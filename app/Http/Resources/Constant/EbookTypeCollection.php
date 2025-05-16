<?php

namespace App\Http\Resources\Constant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EbookTypeCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = EbookTypeResource::class;

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
