<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\CurrencyCast;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'sparepart_id',
        'distributor_id',
        'qty',
        'purchase_price',
        'total_price',
        'purchase_date',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'purchase_price' => CurrencyCast::class,
        'total_price' => CurrencyCast::class,
    ];

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }
}
