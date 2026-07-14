<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @mixin Builder
 * @mixin Model
 *
 * @method static \Illuminate\Database\Eloquent\Model|null find($id)
 * @method static \Illuminate\Database\Eloquent\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 *
 * @property int $id
 * @property string $name
 * @property string|null $tagline
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property string $timezone
 * @property string $currency
 * @property int $seats
 * @property int $tables_count
 * @property string|null $logo_path
 * @property string $primary_color_hex
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read RestaurantRules|null $rules
 * @property-read Collection|RestaurantHours[] $hours
 * @property-read Collection|User[] $users
 * @property-read Collection|Table[] $tables
 * @property-read Collection|Reservation[] $reservations
 * @property-read Collection|Customer[] $customers
 * @property-read Collection|Notification[] $notifications
 * @property-read Collection|ActivityLog[] $activityLog
 * @property-read Collection|Waitlist[] $waitlist
 * @property-read Collection|EmailTemplate[] $emailTemplates
 */
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
        'primary_color_hex',
    ];

    public function getTimezone(): string
    {
        return $this->timezone ?? 'Africa/Harare';
    }

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
