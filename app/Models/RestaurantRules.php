<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
 * @property int $restaurant_id
 * @property int $max_party_size
 * @property int $slot_length_minutes
 * @property int $buffer_minutes
 * @property int $cancellation_window_hours
 * @property int|null $deposit_required_above
 * @property float $avg_spend_per_person
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Restaurant $restaurant
 */
class RestaurantRules extends Model
{
    protected $table = 'restaurant_rules';

    protected $fillable = [
        'restaurant_id',
        'max_party_size',
        'slot_length_minutes',
        'buffer_minutes',
        'cancellation_window_hours',
        'deposit_required_above',
        'avg_spend_per_person',
    ];

    protected $casts = [
        'avg_spend_per_person' => 'decimal:2',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
