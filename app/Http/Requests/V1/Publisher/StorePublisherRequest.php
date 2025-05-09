<?php

namespace App\Http\Requests\V1\Publisher;

use Illuminate\Foundation\Http\FormRequest;

class StorePublisherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'PublisherName' => 'required|string|max:255|unique:publishers,PublisherName',
            'Address' => 'nullable|string|max:500',
            'ContactInfo' => 'nullable|string|max:255',
        ];
    }
}