<?php

namespace App\Http\Requests\V1\ReadingList;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingListRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'nullable|boolean',
            'book_items' => 'nullable|array',
            'book_items.*' => 'exists:book_items,id',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A title for the reading list is required.',
            'book_items.*.exists' => 'One or more of the selected book items does not exist.',
        ];
    }
}
