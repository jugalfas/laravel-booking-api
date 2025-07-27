<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'talent_id',
        'client_id',
        'service_id',
        'price',
    ];

    /**
     * Get the talent (user) for the booking.
     */
    public function talent()
    {
        return $this->belongsTo(User::class, 'talent_id');
    }

    /**
     * Get the client (user) for the booking.
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the service for the booking.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
