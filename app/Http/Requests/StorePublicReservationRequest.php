<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public endpoint - no authentication required
    }

    public function rules(): array
    {
        return [
            'guest_name' => 'required|string|max:120',
            'guest_phone' => 'required|string|max:40',
            'guest_email' => 'required|email|max:160', // required for public booking
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'party_size' => 'required|integer|min:1|max:14',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'guest_name.required' => 'Full name is required.',
            'guest_name.max' => 'Full name cannot exceed 120 characters.',
            'guest_phone.required' => 'Phone number is required.',
            'guest_email.required' => 'Email address is required.',
            'guest_email.email' => 'Please enter a valid email address.',
            'date.required' => 'Date is required.',
            'date.after_or_equal' => 'Date cannot be in the past.',
            'time.required' => 'Time is required.',
            'party_size.required' => 'Party size is required.',
            'party_size.min' => 'Party size must be at least 1.',
            'party_size.max' => 'Party size cannot exceed 14.',
        ];
    }
}
