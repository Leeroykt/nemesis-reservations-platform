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
 * @property string $code
 * @property string|null $zone
 * @property int $capacity
 * @property string $shape
 * @property float $pos_x
 * @property float $pos_y
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Reservation[] $reservations
 */
class Table extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'code',
        'zone',
        'capacity',
        'shape',
        'pos_x',
        'pos_y',
        'status',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}