<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'booking_id', 'mekanik_id', 'kasir_id',
        'total_service', 'total_sparepart', 'grand_total',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function mekanik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mekanik_id')->withTrashed();
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id')->withTrashed();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function transactionSpareparts(): HasMany
    {
        return $this->hasMany(TransactionSparepart::class);
    }
}
