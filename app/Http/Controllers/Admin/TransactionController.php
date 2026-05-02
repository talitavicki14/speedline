<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['booking.user', 'booking.vehicle', 'mekanik', 'kasir', 'payment']);

        if ($request->search) {
            $query->whereHas('booking.user', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        $perPage      = in_array($request->per_page, [10, 25, 50]) ? (int) $request->per_page : 10;
        $transactions = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        return view('admin.transactions.index', compact('transactions', 'perPage'));
    }

    public function create(Booking $booking)
    {
        if ($booking->transaction) {
            return redirect()->route('admin.transactions.show', $booking->transaction)
                             ->with('info', 'Transaksi sudah ada.');
        }

        if ($booking->status !== 'ready') {
            return redirect()->route('admin.bookings.show', $booking)
                             ->with('error', 'Booking harus dalam status "Siap" sebelum membuat transaksi.');
        }

        $booking->load([
            'user', 'vehicle',
            'bookingServices.service',
            'transactionSpareparts.sparepart',
        ]);

        $mechanics = User::where('role', 'mekanik')->get();

        return view('admin.transactions.create', compact('booking', 'mechanics'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'mekanik_id' => 'required|exists:users,id',
        ]);

        $transaction = null;

        DB::transaction(function () use ($request, &$transaction) {
            $booking = Booking::with(['bookingServices', 'transactionSpareparts'])->findOrFail($request->booking_id);

            if ($booking->transaction) {
                $transaction = $booking->transaction;
                return;
            }

            $totalService   = $booking->bookingServices->sum('price');
            $totalSparepart = $booking->transactionSpareparts->sum('subtotal');
            $grandTotal     = $totalService + $totalSparepart;

            $transaction = Transaction::create([
                'booking_id'      => $booking->id,
                'mekanik_id'      => $request->mekanik_id,
                'kasir_id'        => Auth::id(),
                'total_service'   => $totalService,
                'total_sparepart' => $totalSparepart,
                'grand_total'     => $grandTotal,
            ]);

            // Link existing booking spareparts to this transaction
            $booking->transactionSpareparts()->update(['transaction_id' => $transaction->id]);

            Payment::create([
                'transaction_id' => $transaction->id,
                'amount_paid'    => 0,
                'payment_status' => 'unpaid',
            ]);
        });

        return redirect()->route('admin.transactions.show', $transaction)
                         ->with('success', 'Transaksi berhasil dibuat.');
    }

    public function show(Transaction $transaction)
    {
        $transaction->load([
            'booking.user',
            'booking.vehicle',
            'booking.bookingServices.service',
            'transactionSpareparts.sparepart',
            'mekanik', 'kasir', 'payment',
        ]);

        return view('admin.transactions.show', compact('transaction'));
    }
}
