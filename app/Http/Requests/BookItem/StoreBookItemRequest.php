<?php

namespace App\Http\Requests\BookItem;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookItemRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',
            'grade_id' => 'required|exists:grades,id',
            'library_id' => 'required|exists:libraries,id',
            'category_id' => 'required|exists:categories,id',
            'language_id' => 'required|exists:languages,id',
            'subject_id' => 'required|exists:subjects,id',
        ];
    }
}
