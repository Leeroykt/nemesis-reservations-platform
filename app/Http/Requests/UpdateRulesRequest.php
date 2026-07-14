<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'max_party_size' => 'sometimes|integer|min:1|max:50',
            'slot_length_minutes' => 'sometimes|integer|min:30|max:180',
            'buffer_minutes' => 'sometimes|integer|min:0|max:60',
            'cancellation_window_hours' => 'sometimes|integer|min:0|max:48',
            'deposit_required_above' => 'nullable|integer|min:0|max:20',
            'avg_spend_per_person' => 'sometimes|numeric|min:0|max:1000',
        ];
    }
}