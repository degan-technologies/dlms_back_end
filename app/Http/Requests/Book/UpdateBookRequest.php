<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'user_id' => 'sometimes|required|exists:users,id',
            'cover_image' => 'nullable|string',
            'edition' => 'nullable|string',
            'pages' => 'nullable|integer',
            'is_borrowable' => 'boolean',
            'is_reserved' => 'boolean',
            'library_id' => 'sometimes|required|exists:libraries,id',
            'shelf_id' => 'sometimes|required|exists:shelves,id',
            'publication_year' => 'nullable|integer',
            
            // Book condition
            'condition' => 'nullable|string|max:255',
            'condition_note' => 'nullable|string',
        ];
    }
}
