<?php

namespace App\Http\Resources\V1\Subject;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubjectCollection extends ResourceCollection
{
    public $collects = SubjectResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection
        ];
    }
}
