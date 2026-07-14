<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @mixin Builder
 * @mixin Model
 *
 * @method static \Illuminate\Database\Eloquent\Model|null find($id)
 * @method static \Illuminate\Database\Eloquent\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $restaurant_id
 * @property string $role
 * @property string|null $avatar_initials
 * @property string|null $last_login_at
 * @property string|null $remember_token
 * @property Carbon|null $email_verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Restaurant $restaurant
 *
 * @method \Laravel\Sanctum\NewAccessToken createToken(string $name, array $abilities = ['*'])
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'restaurant_id',
        'role',
        'avatar_initials',
        'last_login_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'role' => 'string',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
