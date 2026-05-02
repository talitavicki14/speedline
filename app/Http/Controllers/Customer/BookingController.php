<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Enums\BookingConfig;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Auth::user()->bookings()
            ->with(['vehicle', 'services', 'transaction.payment'])
            ->orderByDesc('created_at')
            ->paginate(10);
        return view('customer.bookings.index', compact('bookings'));
    }

    public function create()
    {
        $vehicles = Auth::user()->vehicles;
        $services = Service::all();
        if ($vehicles->isEmpty()) {
            return redirect()->route('customer.vehicles.index')->with('warning', 'Silakan tambah kendaraan terlebih dahulu.');
        }
        $lastSlot = BookingConfig::lastSlot();
        $minDate = now()->format('H:i') > $lastSlot ? now()->addDay()->format('Y-m-d') : now()->format('Y-m-d');

        return view('customer.bookings.create', compact('vehicles', 'services', 'minDate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|in:' . implode(',', BookingConfig::getSlots()),
            'complaint' => 'nullable|string',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
        ]);

        if ($request->booking_date === now()->format('Y-m-d')) {
            $currentHour = now()->format('H:i');
            if ($request->booking_time < $currentHour) {
                return back()->withInput()->withErrors(['booking_time' => 'Waktu yang dipilih sudah terlewat untuk hari ini.']);
            }
        }

        $vehicle = Auth::user()->vehicles()->findOrFail($request->vehicle_id);

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'vehicle_id' => $vehicle->id,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
            'complaint' => $request->complaint,
            'status' => 'pending',
        ]);

        if ($request->service_ids) {
            foreach ($request->service_ids as $sid) {
                $service = Service::find($sid);
                if ($service) {
                    BookingService::create([
                        'booking_id' => $booking->id,
                        'service_id' => $sid,
                        'price' => $service->price,
                    ]);
                }
            }
        }

        return redirect()->route('customer.bookings.index')->with('success', 'Booking berhasil diajukan!');
    }

    public function show(Booking $booking)
    {
        abort_if($booking->user_id !== Auth::id(), 403);
        $booking->load(['vehicle', 'services', 'transaction.payment', 'transactionSpareparts.sparepart']);
        return view('customer.bookings.show', compact('booking'));
    }

    public function cancel(Booking $booking)
    {
        abort_if($booking->user_id !== Auth::id(), 403);
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()->back()->with('error', 'Booking ini tidak dapat dibatalkan.');
        }
        $booking->update(['status' => 'cancelled']);
        return redirect()->route('customer.bookings.index')->with('success', 'Booking berhasil dibatalkan.');
    }

    public function invoice(Booking $booking)
    {
        abort_if($booking->user_id !== Auth::id(), 403);
        abort_if(!$booking->transaction, 404, 'Transaction not found.');

        $booking->load([
            'user', 'vehicle',
            'services',
            'transactionSpareparts.sparepart',
            'transaction.payment',
            'transaction.mekanik'
        ]);

        $pdf = Pdf::loadView('customer.bookings.pdf.invoice', compact('booking'))
                  ->setPaper('a4', 'portrait');

        $filename = 'Invoice_#TRX-' . str_pad($booking->transaction->id, 4, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->stream($filename);
    }
}
