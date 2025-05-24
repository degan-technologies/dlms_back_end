<?php

namespace App\Http\Requests\V1\BookItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookItemRequest extends FormRequest
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
        $bookItemId = request()->route('bookItem') ? request()->route('bookItem')->id : null;


        
        return [
            'title' => 'sometimes|string|max:255',
            'isbn' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('book_items', 'isbn')->ignore($bookItemId)
            ],
            'item_type' => ['sometimes', 'string', Rule::in(['physical', 'ebook', 'other'])],
            'availability_status' => ['sometimes', 'string', Rule::in(['available', 'loaned', 'reserved', 'under_maintenance', 'lost'])],
            'author' => 'sometimes|string|max:255',
            'publication_year' => 'sometimes|integer|min:1000|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|string|max:2048',
            'language' => 'nullable|string|max:50',
            'library_branch_id' => 'sometimes|exists:library_branches,id',
            'shelf_id' => 'nullable|exists:shelves,id',
            'category_id' => 'sometimes|exists:categories,id',
            'publisher_id' => 'sometimes|exists:publishers,PublisherID',
        ];
    }
}