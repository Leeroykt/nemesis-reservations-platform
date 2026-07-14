<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:160|unique:users,email',
            'password' => 'required|string|min:8|max:255',
            'role' => [
                'required',
                Rule::in(['owner', 'manager', 'host']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'A user with this email already exists.',
            'password.min' => 'Password must be at least 8 characters.',
            'role.in' => 'Role must be owner, manager, or host.',
        ];
    }
}