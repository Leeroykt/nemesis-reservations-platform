<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @mixin Builder
 *
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder whereIn($column, $values, $boolean = 'and')
 * @method static Builder whereNull($column, $boolean = 'and')
 * @method static Builder whereNotNull($column, $boolean = 'and')
 * @method static EmailTemplate|null first()
 * @method static EmailTemplate find($id)
 * @method static EmailTemplate findOrFail($id)
 *
 * @property int $id
 * @property int $restaurant_id
 * @property string $key
 * @property string $name
 * @property string $subject
 * @property string $body
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class EmailTemplate extends Model
{
    protected $fillable = [
        'restaurant_id',
        'key',
        'name',
        'subject',
        'body',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
