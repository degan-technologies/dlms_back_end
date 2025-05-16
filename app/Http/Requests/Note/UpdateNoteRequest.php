<?php

namespace App\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Make sure the user is the owner of the note
        return auth()->check() && $this->route('note')->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => 'sometimes|required|string',
            'page_number' => 'sometimes|required|integer|min:1',
            'highlight_text' => 'nullable|string',
            'metadata' => 'nullable|string',
        ];
    }
}
