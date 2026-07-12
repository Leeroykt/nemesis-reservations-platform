<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'name',
        'email',
        'phone',
        'visits',
        'last_visit_at',
        'is_vip',
        'lifetime_spend'
    ];

    protected $casts = [
        'is_vip' => 'boolean',
        'last_visit_at' => 'date',
        'lifetime_spend' => 'decimal:2'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function preferences()
    {
        return $this->hasMany(CustomerPreference::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}