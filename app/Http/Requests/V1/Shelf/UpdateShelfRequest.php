<?php

namespace App\Http\Requests\V1\Shelf;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShelfRequest extends FormRequest
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
        // $shelfId = $this->route('shelf');
        $shelfId = request()->route('shelf') ? request()->route('shelf')->id : null;

        
        return [
            'code' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('shelves', 'code')->ignore($shelfId)
            ],
            'location' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'is_active' => 'boolean',
            'section_id' => 'sometimes|exists:sections,id',
            'library_branch_id' => 'sometimes|exists:library_branches,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'code.unique' => 'A shelf with this code already exists.',
            'section_id.exists' => 'The selected section does not exist.',
            'library_branch_id.exists' => 'The selected library branch does not exist.'
        ];
    }
}