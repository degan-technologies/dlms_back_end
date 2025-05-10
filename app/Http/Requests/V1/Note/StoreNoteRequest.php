<?php

namespace App\Http\Requests\V1\Note;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoteRequest extends FormRequest
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
            'notable_id' => 'required|integer',
            'type' => ['required', Rule::in(['ebook', 'other_asset'])],
            'content' => 'required|string',
            'page_number' => 'nullable|integer|min:1',
            'position' => 'nullable|string',
            'highlight_text' => 'nullable|string',
            'color' => 'nullable|string|max:30',
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
            'type.in' => 'The type must be either "ebook" or "other_asset".',
            'content.required' => 'The note content is required.',
        ];
    }
}
