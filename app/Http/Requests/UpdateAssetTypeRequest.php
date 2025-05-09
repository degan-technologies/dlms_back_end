<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetTypeRequest extends FormRequest
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
            'name' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('asset_types')->ignore(request()->route()->parameter('asset_type'))
            ],
            'description' => 'nullable|string',
            'is_electronic' => 'boolean',
            'file_type_category' => 'nullable|string|required_if:is_electronic,true',
            'allowed_extensions' => 'nullable|array|required_if:is_electronic,true',
            'allowed_extensions.*' => 'string|max:10',
            'max_file_size' => 'nullable|integer|min:1|required_if:is_electronic,true',
            'icon' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
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
            'name.unique' => 'An asset type with this name already exists',
            'file_type_category.required_if' => 'File type category is required for electronic assets',
            'allowed_extensions.required_if' => 'Allowed file extensions are required for electronic assets',
            'max_file_size.required_if' => 'Maximum file size is required for electronic assets',
        ];
    }
}