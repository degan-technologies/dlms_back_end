<?php

namespace App\Http\Requests\V1\Language;

use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:languages,name',
            'code' => 'required|string|max:10|unique:languages,code',
        ];
    }
}
