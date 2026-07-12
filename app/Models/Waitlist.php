<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// <-- ADD THIS

class Waitlist extends Model
{
    protected $table = 'waitlist';  // <-- ADD THIS

    protected $fillable = [
        'restaurant_id',
        'name',
        'phone',
        'party_size',
        'quoted_wait_minutes',
        'notes',
        'status',
        'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
