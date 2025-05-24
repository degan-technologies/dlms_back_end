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
            'edition' => 'sometimes|string|max:50',
            'isbn' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('books')->ignore($this->route('book')),
            ],
            'title' => 'sometimes|string|max:255',
            'pages' => 'nullable|integer',
            'is_borrowable' => 'sometimes|boolean',
            'book_item_id' => 'sometimes|exists:book_items,id',
            'shelf_id' => 'nullable|exists:shelves,id',
            'library_id' => 'sometimes|exists:libraries,id',
            
            // Book condition
            'condition' => 'nullable|string|max:255',
            'condition_note' => 'nullable|string',
        ];
    }
}
