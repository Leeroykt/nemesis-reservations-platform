<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:120',
            'subject' => 'sometimes|string|max:160',
            'body' => 'sometimes|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'subject.max' => 'Subject cannot exceed 160 characters.',
            'body.max' => 'Body cannot exceed 5000 characters.',
        ];
    }
}