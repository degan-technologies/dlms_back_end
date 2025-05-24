<?php

namespace App\Http\Requests\V1\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_name' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('categories', 'category_name')->ignore($this->category),
            ],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    if ($value == $this->category->id) {
                        $fail('A category cannot be its own parent.');
                    }

                    // Prevent circular reference
                    $parent = \App\Models\Category::find($value);
                    while ($parent && $parent->parent_id) {
                        if ($parent->parent_id == $this->category->id) {
                            $fail('Creating a circular reference is not allowed.');
                            break;
                        }
                        $parent = $parent->parent;
                    }
                },
            ],
        ];
    }
}
