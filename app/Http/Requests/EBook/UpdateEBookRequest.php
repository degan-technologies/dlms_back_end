<?php

namespace App\Http\Requests\EBook;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEBookRequest extends FormRequest
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
            'book_item_id' => 'sometimes|exists:book_items,id',
            'file_path' => 'sometimes|string|max:512',
            'file_format' => 'nullable|string|max:20',
            'file_name' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'file_size_mb' => 'nullable|numeric',
            'pages' => 'nullable|integer',
            'is_downloadable' => 'sometimes|boolean',
            'e_book_type_id' => 'sometimes|exists:e_book_types,id',
        ];
    }
}
