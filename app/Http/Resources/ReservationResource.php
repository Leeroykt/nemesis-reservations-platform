<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'public_ref' => $this->public_ref,
            'guest_name' => $this->guest_name,
            'guest_phone' => $this->guest_phone,
            'guest_email' => $this->guest_email,
            'date' => $this->date,
            'time' => $this->time,
            'party_size' => $this->party_size,
            'status' => $this->status, // ✅ FIXED
            'notes' => $this->notes,
            'source' => $this->source,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'table' => new TableResource($this->whenLoaded('table')),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}