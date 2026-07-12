<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'restaurant_id',
        'actor_user_id',
        'actor_label',
        'icon',
        'tone',
        'description',
        'entity_type',
        'entity_id'
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