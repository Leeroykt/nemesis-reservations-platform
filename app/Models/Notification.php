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
 * @property string $title
 * @property string $message
 * @property bool $is_read
 * @property Carbon $created_at
 * @property-read Restaurant $restaurant
 */
class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'restaurant_id',
        'title',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
