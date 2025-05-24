<?php

namespace App\Http\Requests\V1\EBook;

use Illuminate\Foundation\Http\FormRequest;

class StoreEBookRequest extends FormRequest
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
            'category_id' => 'required|exists:categories,id',
            'publisher_id' => 'required|exists:publishers,PublisherID',
            
            // EBook-specific fields
            'file_url' => 'required|string|max:2048',
            'file_format' => 'required|string|in:pdf,epub,mobi,azw,azw3,ibook,cbz,cbr,txt,html,docx,rtf',
            'file_size_mb' => 'required|numeric|min:0.01',
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
            'file_url.required' => 'The file URL is required for eBooks.',
            'file_format.required' => 'The file format is required for eBooks.',
            'file_size_mb.required' => 'The file size is required for eBooks.',
        ];
    }
}