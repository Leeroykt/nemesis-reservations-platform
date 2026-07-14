<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:120',
            'email' => [
                'sometimes',
                'email',
                'max:160',
                Rule::unique('users', 'email')->ignore($this->route('user')->id),
            ],
            'password' => 'nullable|string|min:8|max:255',
            'role' => [
                'sometimes',
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