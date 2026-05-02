<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Casts\CurrencyCast;

class Payment extends Model
{
    protected $fillable = [
        'transaction_id', 'payment_date', 'amount_paid', 'payment_method',
        'payment_status', 'midtrans_order_id', 'midtrans_transaction_id',
        'midtrans_token', 'midtrans_redirect_url'
    ];

    protected $casts = [
        'amount_paid' => CurrencyCast::class,
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
