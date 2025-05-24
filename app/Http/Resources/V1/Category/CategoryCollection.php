<?php

namespace App\Http\Resources\V1\Category;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
{
    /**
     * The resource that this collection wraps.
     *
     * @var string
     */
    public $collects = CategoryResource::class;

    public function toArray($request)
    {
        $resource = $this->resource;
        // Fix: If resource is not paginated, fallback to collection
        if (is_object($resource) && method_exists($resource, 'total')) {
            return [
                'data' => $this->collection,
                'meta' => [
                    'total'        => $resource->total(),
                    'count'        => $resource->count(),
                    'per_page'     => $resource->perPage(),
                    'current_page' => $resource->currentPage(),
                    'total_pages'  => $resource->lastPage(),
                ],
                'links' => [
                    'first' => $resource->url(1),
                    'last'  => $resource->url($resource->lastPage()),
                    'prev'  => $resource->previousPageUrl(),
                    'next'  => $resource->nextPageUrl(),
                ],
            ];
        } else {
            return [
                'data' => $this->collection,
                'meta' => [
                    'total'        => $this->collection->count(),
                    'count'        => $this->collection->count(),
                    'per_page'     => $this->collection->count(),
                    'current_page' => 1,
                    'total_pages'  => 1,
                ],
                'links' => null,
            ];
        }
    }
}
