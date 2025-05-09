<?php

namespace App\Http\Requests\V1\OtherAsset;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOtherAssetRequest extends FormRequest
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
        
        $otherAsset = request()->route('otherAsset');
        $bookItem = $otherAsset ? $otherAsset->bookItem : null;

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
            'shelf_id' => 'nullable|exists:shelves,id',
            'category_id' => 'sometimes|exists:categories,id',
            'publisher_id' => 'sometimes|exists:publishers,PublisherID',
            
            // OtherAsset-specific fields
            'asset_type_id' => 'sometimes|exists:asset_types,id',
            'media_type' => 'nullable|string|max:50',
            'unique_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('other_assets', 'unique_id')->ignore($otherAsset ? $otherAsset->id : null)
            ],
            'duration_minutes' => 'nullable|integer|min:0',
            'manufacturer' => 'nullable|string|max:100',
            'physical_condition' => 'nullable|string|in:excellent,good,fair,poor,damaged',
            'location_details' => 'nullable|string|max:255',
            'acquisition_date' => 'nullable|date',
            'usage_instructions' => 'nullable|string',
            'restricted_access' => 'boolean',
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
            'isbn.unique' => 'An asset with this ISBN already exists.',
            'unique_id.unique' => 'An asset with this unique ID already exists.',
            'asset_type_id.exists' => 'The selected asset type is invalid.',
        ];
    }
}