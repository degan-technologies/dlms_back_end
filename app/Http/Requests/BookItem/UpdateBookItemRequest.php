<?php

namespace App\Http\Requests\BookItem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookItemRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|string|max:1024',
            'language_id' => 'sometimes|exists:languages,id',
            'category_id' => 'sometimes|exists:categories,id',
            'grade' => 'nullable|string|max:50',
            'library_id' => 'sometimes|exists:libraries,id',
            'shelf_id' => 'nullable|exists:shelves,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ];
    }
}
