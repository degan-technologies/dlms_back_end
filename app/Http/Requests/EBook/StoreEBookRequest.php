<?php

namespace App\Http\Requests\EBook;

use Illuminate\Foundation\Http\FormRequest;

class StoreEBookRequest extends FormRequest
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
            'book_item_id' => 'required|exists:book_items,id',
            'file_name' => 'required|string|max:255',
            'file_path' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:102400',
            'is_downloadable' => 'nullable|string|in:true,false',
            'e_book_type_id' => 'required|exists:e_book_types,id',
        ];
    }
}
