<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['transaction.booking.user']);

        $status = $request->status;
        if ($status && in_array($status, ['partial', 'paid', 'expired', 'failed'])) {
            $query->where('payment_status', $status);
        } else {
            $query->whereIn('payment_status', ['paid', 'partial', 'expired', 'failed']);
        }

        $perPage  = in_array($request->per_page, [10, 25, 50]) ? (int) $request->per_page : 10;
        $payments = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();
        return view('admin.payments.index', compact('payments', 'perPage', 'status'));
    }

    public function processCash(Request $request, Payment $payment)
    {
        Gate::authorize('manage-data');
        $amountPaid = str_replace('.', '', $request->amount_paid);
        $request->merge(['amount_paid' => $amountPaid]);

        $request->validate(['amount_paid' => 'required|min:0']);

        $currentPaid = $payment->amount_paid ?? 0;
        $received    = $request->amount_paid;
        $newTotal    = $currentPaid + $received;

        $paymentStatus = $newTotal >= $payment->transaction->grand_total ? 'paid' : 'partial';

        $payment->update([
            'payment_date'   => now()->toDateString(),
            'amount_paid'    => $newTotal,
            'payment_method' => 'cash',
            'payment_status' => $paymentStatus,
        ]);

        $booking = $payment->transaction->booking;
        if ($booking && $booking->status === 'ready') {
            $booking->update(['status' => 'completed']);
        }

        return redirect()->back()->with('success', 'Pembayaran tunai berhasil dicatat. Status booking diperbarui menjadi selesai.');
    }
}
