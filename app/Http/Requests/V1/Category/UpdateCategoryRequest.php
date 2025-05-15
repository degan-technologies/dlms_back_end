<?php

namespace App\Http\Requests\V1\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($this->category)
            ],
            'slug' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('categories', 'slug')->ignore($this->category)
            ],
            'description' => 'nullable|string|max:500',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    // Prevent category from being its own parent
                    if ($value == $this->category->id) {
                        $fail('A category cannot be its own parent.');
                    }
                    
                    // Prevent circular references
                    if ($value && $this->category->id) {
                        $parent = $this->category->find($value);
                        while ($parent && $parent->parent_id) {
                            if ($parent->parent_id == $this->category->id) {
                                $fail('Creating a circular reference is not allowed.');
                                break;
                            }
                            $parent = $parent->parent;
                        }
                    }
                }
            ],
            'icon' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean'
        ];
    }
}
