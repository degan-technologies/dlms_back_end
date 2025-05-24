<?php

namespace App\Http\Requests\V1\Subject;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $subjectId = $this->route('subject')->id ?? null;
        return [
            'name' => 'required|string|max:255|unique:subjects,name,' . $subjectId,
        ];
    }
}
