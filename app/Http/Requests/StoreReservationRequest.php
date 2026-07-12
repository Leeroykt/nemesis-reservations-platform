<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name' => 'required|string|max:120',
            'guest_phone' => 'required|string|max:40',
            'guest_email' => 'nullable|email|max:160',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'party_size' => 'required|integer|min:1',
            'table_id' => 'nullable|exists:tables,id',
            'notes' => 'nullable|string',
            'source' => 'nullable|in:Website,Phone,App,Walk-in',
            'status' => 'nullable|in:Upcoming,Confirmed,Completed,Cancelled',
        ];
    }
}