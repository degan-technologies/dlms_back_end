<?php

namespace App\Http\Requests\V1\EBook;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEBookRequest extends FormRequest
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
        $ebook = request()->route('ebook');
        $bookItem = $ebook ? $ebook->bookItem : null;


        return [
            // BookItem fields
            'title' => 'sometimes|string|max:255',
            'isbn' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('book_items', 'isbn')->ignore($bookItem ? $bookItem->id : null)
            ],
            'availability_status' => 'sometimes|string|in:available,loaned,reserved,under_maintenance,lost',
            'author' => 'sometimes|string|max:255',
            'publication_year' => 'sometimes|integer|min:1000|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|string|max:2048',
            'language' => 'nullable|string|max:50',
            'library_branch_id' => 'sometimes|exists:library_branches,id',
            'category_id' => 'sometimes|exists:categories,id',
            'publisher_id' => 'sometimes|exists:publishers,PublisherID',
            
            // EBook-specific fields
            'file_url' => 'sometimes|string|max:2048',
            'file_format' => 'sometimes|string|in:pdf,epub,mobi,azw,azw3,ibook,cbz,cbr,txt,html,docx,rtf',
            'file_size_mb' => 'sometimes|numeric|min:0.01',
            'pages' => 'nullable|integer|min:1',
            'is_downloadable' => 'boolean',
            'requires_authentication' => 'boolean',
            'drm_type' => 'nullable|string|max:50',
            'access_expires_at' => 'nullable|date',
            'max_downloads' => 'nullable|integer|min:0',
            'reader_app' => 'nullable|string|max:100',
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
            'isbn.unique' => 'An eBook with this ISBN already exists.',
            'publication_year.max' => 'Publication year cannot be in the future.',
        ];
    }
}