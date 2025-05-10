<?php

namespace App\Http\Requests\V1\Note;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
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
            'content' => 'nullable|string',
            'page_number' => 'nullable|integer|min:1',
            'position' => 'nullable|string',
            'highlight_text' => 'nullable|string',
            'color' => 'nullable|string|max:30',
            'metadata' => 'nullable|array',
        ];
    }
}
