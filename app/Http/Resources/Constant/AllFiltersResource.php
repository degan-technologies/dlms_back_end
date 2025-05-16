<?php

namespace App\Http\Resources\Constant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllFiltersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'categories' => CategoryResource::collection($this['categories']),
            'languages' => LanguageResource::collection($this['languages']),
            // 'libraries' => LibraryResource::collection($this['libraries']),
            'subjects' => SubjectResource::collection($this['subjects']),
            'ebook_types' => EbookTypeResource::collection($this['ebook_types']),
        ];
    }
}
