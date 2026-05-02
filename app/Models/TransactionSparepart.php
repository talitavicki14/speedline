<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Casts\CurrencyCast;

class TransactionSparepart extends Model
{
    protected $fillable = [
        'transaction_id', 
        'booking_id', 
        'sparepart_id', 
        'qty', 
        'price', 
        'subtotal'
    ];

    protected $casts = [
        'price' => CurrencyCast::class,
        'subtotal' => CurrencyCast::class,
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }
}
