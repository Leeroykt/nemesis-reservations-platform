<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'primary_color_hex' => 'sometimes|string|regex:/^#[a-fA-F0-9]{6}$/',
            'logo' => 'nullable|image|mimes:jpeg,png,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'primary_color_hex.regex' => 'Please enter a valid hex color code (e.g., #C9A227).',
            'logo.image' => 'The file must be an image.',
            'logo.mimes' => 'Allowed formats: JPEG, PNG, SVG.',
            'logo.max' => 'Logo must not exceed 2MB.',
        ];
    }
}