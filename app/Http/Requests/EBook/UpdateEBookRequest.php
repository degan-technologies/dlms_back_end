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
            'book_item_id' => 'sometimes|required|exists:book_items,id',
            'file_name' => 'sometimes|required|string|max:255',
            'user_id' => 'sometimes|required|exists:users,id',
            'file_path' => 'sometimes|required|string',
            'file_format' => 'sometimes|required|string',
            'file_size_mb' => 'nullable|numeric',
            'pages' => 'nullable|integer',
            'is_downloadable' => 'boolean',
            'e_book_type_id' => 'sometimes|required|exists:e_book_types,id',
        ];
    }
}
