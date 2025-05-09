<?php

namespace App\Http\Requests\V1\AssetType;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetTypeRequest extends FormRequest
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
            'name' => 'required|string|max:100|unique:asset_types,name',
            'description' => 'nullable|string',
            'requires_special_handling' => 'boolean',
            'is_electronic' => 'boolean',
            'icon' => 'nullable|string|max:50',
            'metadata' => 'nullable|json',
            'is_active' => 'boolean',
        ];
    }
}