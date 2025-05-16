<?php

namespace App\Http\Requests\EBook;

use Illuminate\Foundation\Http\FormRequest;

class StoreEBookRequest extends FormRequest
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
            // BookItem attributes
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|string|max:1024',
            'language_id' => 'required|exists:languages,id',
            'category_id' => 'required|exists:categories,id',
            'grade' => 'nullable|string|max:50',
            'library_id' => 'required|exists:libraries,id',
            'subject_id' => 'nullable|exists:subjects,id',
            
            // EBook specific attributes
            'file_path' => 'required|string|max:512',
            'file_format' => 'nullable|string|max:20',
            'file_name' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'file_size_mb' => 'nullable|numeric',
            'pages' => 'nullable|integer',
            'is_downloadable' => 'boolean',
            'e_book_type_id' => 'required|exists:e_book_types,id',
        ];
    }
}
