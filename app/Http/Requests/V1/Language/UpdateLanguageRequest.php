<?php

namespace App\Http\Requests\V1\Language;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $languageId = $this->route('language')->id ?? null;
        return [
            'name' => 'required|string|max:255|unique:languages,name,' . $languageId,
            'code' => 'required|string|max:10|unique:languages,code,' . $languageId,
        ];
    }
}
