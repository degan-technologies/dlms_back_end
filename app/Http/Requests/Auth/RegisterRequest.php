<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'phone_no' => 'required|string|max:20|unique:users,phone_no',
            'password' => ['required', 'string', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()],
            'library_branch_id' => 'required|exists:library_branches,id',
            'role' => 'required|string|exists:roles,name',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username is required',
            'username.unique' => 'This username is already taken',
            'email.unique' => 'This email address is already registered',
            'phone_no.required' => 'Phone number is required',
            'phone_no.unique' => 'This phone number is already registered',
            'library_branch_id.required' => 'Library branch is required',
            'library_branch_id.exists' => 'Selected library branch is invalid',
            'role.required' => 'User role is required',
            'role.exists' => 'Selected role is invalid',
        ];
    }
}