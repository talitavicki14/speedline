<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Casts\CurrencyCast;

class Service extends Model
{
    protected $fillable = ['service_name', 'description', 'price', 'estimated_time'];

    protected $casts = [
        'price' => CurrencyCast::class,
    ];

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_services')->withPivot('price')->withTimestamps();
    }
}
