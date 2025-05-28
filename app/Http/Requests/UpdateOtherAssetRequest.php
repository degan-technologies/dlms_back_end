<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOtherAssetRequest extends FormRequest
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
            'asset_type_id' => 'sometimes|exists:asset_types,id',
            'media_type' => 'nullable|string|max:50',
            'unique_id' => 'nullable|string|max:100',
            'duration_minutes' => 'nullable|integer|min:0',
            'manufacturer' => 'nullable|string|max:255',
            'physical_condition' => 'nullable|string|max:50',
            'location_details' => 'nullable|string|max:255',
            'acquisition_date' => 'nullable|date',
            'usage_instructions' => 'nullable|string',
            'restricted_access' => 'boolean',
            
            // BookItem related fields when updating the parent
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'availability_status' => 'sometimes|string|in:available,checked_out,reserved,lost,damaged',
            'cover_image_url' => 'nullable|string',
            'metadata' => 'nullable|array',
            'language' => 'nullable|string|max:50',
            'library_branch_id' => 'sometimes|exists:library_branches,id',
            'shelf_id' => 'nullable|exists:shelves,id',
            'category_id' => 'sometimes|exists:categories,id',
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
            'asset_type_id.exists' => 'The selected asset type is invalid',
            'availability_status.in' => 'The availability status must be one of: available, checked_out, reserved, lost, damaged',
            'library_branch_id.exists' => 'The selected library branch is invalid',
            'category_id.exists' => 'The selected category is invalid',
        ];
    }
}