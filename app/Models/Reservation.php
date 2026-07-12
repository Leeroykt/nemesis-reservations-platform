<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'created_by_user_id'
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'string',
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