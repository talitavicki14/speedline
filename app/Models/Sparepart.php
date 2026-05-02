<?php
namespace App\Models;
use App\Casts\CurrencyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sparepart extends Model
{
    protected $fillable = ['name', 'type', 'brand', 'stock', 'price', 'purchase_price', 'distributor_id'];

    protected $casts = [
        'price' => CurrencyCast::class,
        'purchase_price' => CurrencyCast::class,
    ];

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function transactionSpareparts(): HasMany
    {
        return $this->hasMany(TransactionSparepart::class);
    }
}
