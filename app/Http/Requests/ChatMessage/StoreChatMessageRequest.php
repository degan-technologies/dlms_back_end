<?php

namespace App\Http\Requests\ChatMessage;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatMessageRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        // User must be authenticated to create a chat message
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
            'question' => 'required|string',
            'highlight_text' => 'nullable|string',
            'sent_at' => 'nullable|date_format:H:i:s',
            'page_number' => 'nullable|integer|min:1',
            'is_anonymous' => 'sometimes|boolean'
        ];
    }
}
