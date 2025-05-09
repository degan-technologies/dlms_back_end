<?php

namespace App\Http\Requests\V1\BookItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // You can implement authorization logic here
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|max:20|unique:book_items,isbn',
            'item_type' => ['required', 'string', Rule::in(['physical', 'ebook', 'other'])],
            'availability_status' => ['required', 'string', Rule::in(['available', 'loaned', 'reserved', 'under_maintenance', 'lost'])],
            'author' => 'required|string|max:255',
            'publication_year' => 'required|integer|min:1000|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|string|max:2048',
            'language' => 'nullable|string|max:50',
            'library_branch_id' => 'required|exists:library_branches,id',
            'shelf_id' => 'nullable|exists:shelves,id',
            'category_id' => 'required|exists:categories,id',
            'publisher_id' => 'required|exists:publishers,PublisherID',
        ];
    }
}