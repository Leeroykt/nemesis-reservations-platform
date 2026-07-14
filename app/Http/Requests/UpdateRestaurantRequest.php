<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:120',
            'tagline' => 'nullable|string|max:160',
            'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40',
            'address' => 'nullable|string|max:255',
            'timezone' => 'sometimes|string|max:60',
            'currency' => 'sometimes|string|max:3',
        ];
    }
}