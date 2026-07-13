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
 * @method static \Illuminate\Database\Eloquent\Builder whereIn($column, $values, $boolean = 'and')
 *
 * @property int $id
 * @property int $restaurant_id
 * @property string $public_ref
 * @property int|null $customer_id
 * @property int|null $table_id
 * @property string $guest_name
 * @property string $guest_phone
 * @property string|null $guest_email
 * @property string $date
 * @property string $time
 * @property int $party_size
 * @property string $status
 * @property string|null $notes
 * @property string $source
 * @property int|null $created_by_user_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Restaurant $restaurant
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\Table|null $table
 * @property-read \App\Models\User|null $createdBy
 */
class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'public_ref',
        'customer_id',
        'table_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'date',
        'time',
        'party_size',
        'status',
        'notes',
        'source',
        'created_by_user_id',
    ];

    protected $attributes = [
        'status' => 'Upcoming',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'string',
        'status' => 'string',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}