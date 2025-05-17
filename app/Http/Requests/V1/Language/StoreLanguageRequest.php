<?php

namespace App\Http\Requests\V1\Language;

use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'name' => 'required|string|max:50|unique:languages,name',
            'code' => 'required|string|max:10|unique:languages,code',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'A language name is required.',
            'name.unique' => 'This language name already exists.',
            'code.required' => 'A language code is required.',
            'code.unique' => 'This language code already exists.',
        ];
    }
}
