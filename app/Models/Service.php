<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'duration',
        'discount',
        'advance_payment',
        'advance_payment_value',
        'advance_payment_type',
    ];

    /**
     * Get the user (talent) that owns the service.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bookings for the service.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
