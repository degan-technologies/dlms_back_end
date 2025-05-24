<?php

namespace App\Http\Requests\V1\Publisher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePublisherRequest extends FormRequest
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
        $publisherId = request()->route('publisher') ? request()->route('publisher')->PublisherID : null;
        
        return [
            'PublisherName' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('publishers', 'PublisherName')->ignore($publisherId, 'PublisherID')
            ],
            'Address' => 'nullable|string|max:500',
            'ContactInfo' => 'nullable|string|max:255',
        ];
    }
}