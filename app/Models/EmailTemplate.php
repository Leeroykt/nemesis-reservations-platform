<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'restaurant_id',
        'key',
        'name',
        'subject',
        'body'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}