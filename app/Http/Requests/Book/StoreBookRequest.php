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
            'book_item_id' => 'required|exists:book_items,id',
            'title' => 'required|string|max:255',
            'isbn'=> 'required|string|max:13',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'edition' => 'nullable|string',
            'pages' => 'nullable|integer',
            'is_borrowable' => 'string|in:true,false',
            'library_id' => 'required|exists:libraries,id',
            'shelf_id' => 'required|exists:shelves,id',
            'publication_year' => 'nullable|integer|digits:4|min:1000|max:' . date('Y'),
        ];
    }
}
