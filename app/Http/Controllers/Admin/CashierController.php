<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Models\Transaction;
use App\Models\TransactionSparepart;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CashierController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-data');
        $spareparts = Sparepart::where('stock', '>', 0)->get();
        return view('admin.cashier.index', compact('spareparts'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-data');
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:spareparts,id',
            'items.*.qty' => 'required|integer|min:1',
            'amount_paid' => 'required|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $totalSparepart = 0;
            $items = [];

            foreach ($request->items as $item) {
                $sparepart = Sparepart::lockForUpdate()->find($item['id']);
                
                if ($sparepart->stock < $item['qty']) {
                    throw new \Exception("Stok {$sparepart->name} tidak mencukupi.");
                }

                $subtotal = $sparepart->price * $item['qty'];
                $totalSparepart += $subtotal;
                
                $items[] = [
                    'sparepart_id' => $sparepart->id,
                    'qty' => $item['qty'],
                    'price' => $sparepart->price,
                    'subtotal' => $subtotal
                ];

                $sparepart->decrement('stock', $item['qty']);
            }

            $transaction = Transaction::create([
                'kasir_id' => Auth::id(),
                'total_service' => 0,
                'total_sparepart' => $totalSparepart,
                'grand_total' => $totalSparepart,
            ]);

            foreach ($items as $item) {
                $item['transaction_id'] = $transaction->id;
                TransactionSparepart::create($item);
            }

            Payment::create([
                'transaction_id' => $transaction->id,
                'payment_date' => now(),
                'amount_paid' => $request->amount_paid,
                'payment_method' => 'cash',
                'payment_status' => $request->amount_paid >= $totalSparepart ? 'paid' : 'partial',
            ]);

            $receiptHtml = view('admin.transactions.receipt.print', [
                'transaction' => $transaction->load(['transactionSpareparts.sparepart', 'kasir', 'payment'])
            ])->render();

            return response()->json([
                'success' => true,
                'receipt' => $receiptHtml,
                'message' => 'Transaksi berhasil disimpan.'
            ]);
        });
    }
}
