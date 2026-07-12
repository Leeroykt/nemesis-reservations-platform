<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    protected $fillable = [
        'restaurant_id',
        'name',
        'phone',
        'party_size',
        'quoted_wait_minutes',
        'notes',
        'status',
        'added_at'
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}