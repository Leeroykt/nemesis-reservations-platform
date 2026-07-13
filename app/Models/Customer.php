<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @method static \Illuminate\Database\Eloquent\Model|null find($id)
 * @method static \Illuminate\Database\Eloquent\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 *
 * @property int $id
 * @property int $restaurant_id
 * @property string $name
 * @property string|null $email
 * @property string $phone
 * @property int $visits
 * @property string|null $last_visit_at
 * @property bool $is_vip
 * @property float $lifetime_spend
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CustomerPreference[] $preferences
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Reservation[] $reservations
 */
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
        'lifetime_spend',
    ];

    protected $casts = [
        'is_vip' => 'boolean',
        'last_visit_at' => 'date',
        'lifetime_spend' => 'decimal:2',
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