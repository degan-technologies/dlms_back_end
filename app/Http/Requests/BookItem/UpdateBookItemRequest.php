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
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'grade_id' => 'sometimes|required|exists:grades,id',
            'library_id' => 'sometimes|required|exists:libraries,id',
            'category_id' => 'sometimes|required|exists:categories,id',
            'language_id' => 'sometimes|required|exists:languages,id',
            'subject_id' => 'sometimes|required|exists:subjects,id',
        ];
    }
}
