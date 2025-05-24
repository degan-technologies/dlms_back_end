<?php

// app/Http/Resources/Constant/GradeResource.php

namespace App\Http\Resources\Constant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
    'id' => $this->id,
    'name' => $this->name,
    'sections' => $this->sections->map(function ($section) {
        return [
            'id' => $section->id,
            'name' => $section->name,
            // 'read_count' => $section->read_count ?? 0,
        ];
    }),
];

    }
}
