<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
            'grade' => 'nullable|string|max:50',
            'library_id' => 'required|exists:libraries,id',
            'shelf_id' => 'nullable|exists:shelves,id',
            'subject_id' => 'nullable|exists:subjects,id',

            // Book specific attributes
            'edition' => 'nullable|string|max:50',
            'isbn' => 'required|string|max:20|unique:books,isbn',
            'pages' => 'nullable|integer',
            'is_borrowable' => 'boolean',
            'publication_year' => 'nullable|year',
            'book_item_id' => 'required|exists:book_items,id',

        ];
    }
}
