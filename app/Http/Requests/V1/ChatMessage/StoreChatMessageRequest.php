<?php

namespace App\Http\Requests\V1\ChatMessage;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authentication handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'question' => 'required|string|min:3|max:1000',
            'is_anonymous' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'question.required' => 'Please enter a question.',
            'question.min' => 'Your question is too short.',
            'question.max' => 'Your question is too long. Please keep it under 1000 characters.',
        ];
    }
}
