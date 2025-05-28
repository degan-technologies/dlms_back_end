<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOtherAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // In a real app, you would check user permissions here
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
            'book_item_id' => 'sometimes|exists:book_items,id',
            'asset_type_id' => 'required|exists:asset_types,id',
            'media_type' => 'nullable|string|max:50',
            'unique_id' => 'nullable|string|max:100',
            'duration_minutes' => 'nullable|integer|min:0',
            'manufacturer' => 'nullable|string|max:255',
            'physical_condition' => 'nullable|string|max:50',
            'location_details' => 'nullable|string|max:255',
            'acquisition_date' => 'nullable|date',
            'usage_instructions' => 'nullable|string',
            'restricted_access' => 'boolean',
            
            // BookItem related fields when creating both at once
            'title' => 'required_without:book_item_id|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'item_type' => 'required_without:book_item_id|string|in:other',
            'availability_status' => 'nullable|string|in:available,checked_out,reserved,lost,damaged',
            'author' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 10),
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|string',
            'metadata' => 'nullable|array',
            'language' => 'nullable|string|max:50',
            'library_branch_id' => 'required_without:book_item_id|exists:library_branches,id',
            'shelf_id' => 'nullable|exists:shelves,id',
            'category_id' => 'required_without:book_item_id|exists:categories,id',
            'publisher_id' => 'nullable|exists:publishers,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'asset_type_id.required' => 'The asset type is required',
            'asset_type_id.exists' => 'The selected asset type is invalid',
            'title.required_without' => 'The title is required when creating a new asset',
            'item_type.in' => 'The item type must be "other" for other assets',
            'library_branch_id.required_without' => 'The library branch is required when creating a new asset',
            'category_id.required_without' => 'The category is required when creating a new asset',
        ];
    }
}