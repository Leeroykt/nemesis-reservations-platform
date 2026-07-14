<?php

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Customer
 */
class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'visits' => $this->visits,
            'last_visit_at' => $this->last_visit_at,
            'is_vip' => $this->is_vip,
            'lifetime_spend' => $this->lifetime_spend,
            'preferences' => $this->whenLoaded('preferences', function () {
                return $this->preferences->pluck('note');
            }, []),
            // Always include the key, even if reservations are not loaded
            'reservations' => $this->whenLoaded('reservations', function () {
                return ReservationResource::collection($this->reservations);
            }, []),
            'created_at' => $this->created_at,
        ];
    }
}
