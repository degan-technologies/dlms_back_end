<?php

namespace App\Http\Requests\V1\Book;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // BookItem fields
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|max:20|unique:book_items,isbn',
            'availability_status' => 'required|string|in:available,loaned,reserved,under_maintenance,lost',
            'author' => 'required|string|max:255',
            'publication_year' => 'required|integer|min:1000|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|string|max:2048',
            'language' => 'nullable|string|max:50',
            'library_branch_id' => 'required|exists:library_branches,id',
            'shelf_id' => 'nullable|exists:shelves,id',
            'category_id' => 'required|exists:categories,id',
            'publisher_id' => 'required|exists:publishers,PublisherID',
            
            // Book-specific fields
            'edition' => 'nullable|string|max:50',
            'pages' => 'nullable|integer|min:1',
            'cover_type' => 'nullable|string|in:hardcover,paperback,spiral,other',
            'dimensions' => 'nullable|string|max:50',
            'weight_grams' => 'nullable|integer|min:1',
            'barcode' => 'nullable|string|max:50|unique:books,barcode',
            'shelf_location_detail' => 'nullable|string|max:100',
            'reference_only' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'isbn.unique' => 'A book with this ISBN already exists.',
            'barcode.unique' => 'A book with this barcode already exists.',
            'publication_year.max' => 'Publication year cannot be in the future.',
        ];
    }
}