<?php

namespace App\Http\Requests\ChatMessage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Make sure the user is the owner of the chat message
        return auth()->check() && $this->route('chatMessage')->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => 'sometimes|required|string',
            'is_anonymous' => 'sometimes|boolean',
        ];
    }
}
