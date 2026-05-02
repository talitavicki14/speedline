<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;
use App\Notifications\VerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'address', 'photo', 'email_verified_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function mechanicTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'mekanik_id');
    }

    public function cashierTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'kasir_id');
    }

    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'owner']);
    }
    public function isMekanik()
    {
        return $this->role === 'mekanik';
    }
    public function isKasir()
    {
        return $this->role === 'kasir';
    }
    public function isCustomer()
    {
        return $this->role === 'customer';
    }
    public function isStaff()
    {
        return in_array($this->role, ['admin', 'owner', 'mekanik', 'kasir']);
    }

    public function sendEmailVerificationNotification()
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->id, 'hash' => sha1($this->getEmailForVerification())]
        );

        $this->notify(new VerifyEmailNotification($url));
    }
}
