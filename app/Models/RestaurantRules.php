<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantRules extends Model
{
    protected $fillable = [
        'restaurant_id',
        'max_party_size',
        'slot_length_minutes',
        'buffer_minutes',
        'cancellation_window_hours',
        'deposit_required_above'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}