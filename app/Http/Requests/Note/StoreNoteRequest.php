<?php

namespace App\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        // User must be authenticated to create a note
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'e_book_id' => 'required|exists:e_books,id',
            'content' => 'required|string',
            'page_number' => 'nullable|integer|min:1',
            'sent_at' => 'nullable|date_format:Y-m-d H:i:s',
            'highlight_text' => 'nullable|string',
        ];
    }
}
