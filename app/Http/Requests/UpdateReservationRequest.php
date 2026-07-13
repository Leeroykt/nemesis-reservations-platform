<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name' => 'sometimes|string|max:120',
            'guest_phone' => 'sometimes|string|max:40',
            'guest_email' => 'nullable|email|max:160',
            'date' => 'sometimes|date_format:Y-m-d|after_or_equal:today',
            'time' => 'sometimes|date_format:H:i',
            'party_size' => 'sometimes|integer|min:1',
            'table_id' => 'nullable|exists:tables,id',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:Upcoming,Confirmed,Completed,Cancelled',
            'source' => 'sometimes|in:Website,Phone,App,Walk-in',
        ];
    }
}
