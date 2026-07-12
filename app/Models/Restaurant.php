<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'name',
        'tagline',
        'email',
        'phone',
        'address',
        'timezone',
        'currency',
        'seats',
        'tables_count',
        'logo_path',
        'primary_color_hex'
    ];

    // ENTERPRISE RULEBOOK §3: Timezone accessor
    public function getTimezone(): string
    {
        return $this->timezone ?? 'Africa/Harare';
    }

    // Relationships
    public function hours()
    {
        return $this->hasMany(RestaurantHours::class);
    }

    public function rules()
    {
        return $this->hasOne(RestaurantRules::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function activityLog()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function waitlist()
    {
        return $this->hasMany(Waitlist::class);
    }

    public function emailTemplates()
    {
        return $this->hasMany(EmailTemplate::class);
    }
}