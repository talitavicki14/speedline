<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'contact_person',
        'is_active',
    ];

    public function spareparts()
    {
        return $this->hasMany(Sparepart::class);
    }
}
