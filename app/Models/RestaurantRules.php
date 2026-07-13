<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $restaurant_id
 * @property int $max_party_size
 * @property int $slot_length_minutes
 * @property int $buffer_minutes
 * @property int $cancellation_window_hours
 * @property int|null $deposit_required_above
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Restaurant $restaurant
 */
class RestaurantRules extends Model
{
    protected $fillable = [
        'restaurant_id',
        'max_party_size',
        'slot_length_minutes',
        'buffer_minutes',
        'cancellation_window_hours',
        'deposit_required_above',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}