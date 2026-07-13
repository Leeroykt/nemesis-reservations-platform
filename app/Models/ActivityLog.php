<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @method static \Illuminate\Database\Eloquent\Model|null find($id)
 * @method static \Illuminate\Database\Eloquent\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Model create(array $attributes = [])
 *
 * @property int $id
 * @property int $restaurant_id
 * @property int|null $actor_user_id
 * @property string $actor_label
 * @property string $icon
 * @property string $tone
 * @property string $description
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\User|null $actor
 */
class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $fillable = [
        'restaurant_id',
        'actor_user_id',
        'actor_label',
        'icon',
        'tone',
        'description',
        'entity_type',
        'entity_id',
    ];

    protected $casts = [
        'tone' => 'string',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}