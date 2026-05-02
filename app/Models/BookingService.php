<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Casts\CurrencyCast;

class BookingService extends Model
{
    protected $fillable = ['booking_id', 'service_id', 'price'];

    protected $casts = [
        'price' => CurrencyCast::class,
    ];

    public function booking(): BelongsTo
    { 
        return $this->belongsTo(Booking::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
