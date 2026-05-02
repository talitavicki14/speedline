<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = ['user_id', 'brand', 'model', 'year', 'license_plate', 'color'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function bookings(): HasMany
    { 
        return $this->hasMany(Booking::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->brand} {$this->model} ({$this->year})"; 
    }
}
