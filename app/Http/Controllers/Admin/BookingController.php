<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TransactionSparepart;
use App\Models\Service;
use App\Models\Sparepart;
use App\Models\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'vehicle', 'services']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->date) {
            $query->whereDate('booking_date', $request->date);
        }
        if ($request->search) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        if (Auth::user()->role === 'mekanik') {
            $query->whereIn('status', ['confirmed', 'in_progress', 'ready']);
        }

        $perPage  = in_array($request->per_page, [10, 25, 50]) ? (int) $request->per_page : 10;
        $bookings = $query->orderByDesc('booking_date')->paginate($perPage)->withQueryString();

        return view('admin.bookings.index', compact('bookings', 'perPage'));
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'user', 'vehicle',
            'services', 'bookingServices.service',
            'transactionSpareparts.sparepart',
            'transaction.payment',
        ]);

        $spareparts = Sparepart::where('stock', '>', 0)->get();
        $services   = Service::all();

        return view('admin.bookings.show', compact('booking', 'spareparts', 'services'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        Gate::authorize('manage-data');
        $request->validate(['status' => 'required|in:pending,confirmed,in_progress,ready,completed,cancelled']);

        $role          = Auth::user()->role;
        $currentStatus = $booking->status;
        $newStatus     = $request->status;
        $allowed       = $this->getAllowedTransitions($role, $currentStatus);

        if (!in_array($newStatus, $allowed)) {
            return redirect()->back()->with('error', 'Anda tidak berwenang untuk melakukan perubahan status ini.');
        }

        if ($newStatus === 'ready' && $booking->bookingServices()->count() === 0) {
            return redirect()->back()->with('error', 'Tidak dapat menandai siap tanpa setidaknya satu layanan.');
        }

        DB::transaction(function() use ($booking, $newStatus) {
            if ($newStatus === 'cancelled') {
                foreach ($booking->transactionSpareparts as $bs) {
                    if ($bs->sparepart) {
                        $bs->sparepart->increment('stock', (int) $bs->qty);
                    }
                }
            }
            $booking->update(['status' => $newStatus]);
        });

        return redirect()->back()->with('success', 'Status booking berhasil diperbarui.');
    }

    private function getAllowedTransitions(string $role, string $currentStatus): array
    {
        $matrix = [
            'pending' => [
                'admin'   => ['confirmed', 'cancelled'],
                'kasir'   => ['confirmed', 'cancelled'],
                'mekanik' => [],
            ],
            'confirmed' => [
                'admin'   => ['in_progress', 'cancelled'],
                'kasir'   => ['cancelled'],
                'mekanik' => ['in_progress'],
            ],
            'in_progress' => [
                'admin'   => ['ready'],
                'mekanik' => ['ready'],
            ],
            'ready' => [
                'admin'   => ['in_progress'],
                'mekanik' => [],
            ],
        ];

        return $matrix[$currentStatus][$role] ?? [];
    }

    public function addService(Request $request, Booking $booking)
    {
        $this->authorizeServiceEdit($booking);

        $request->validate(['service_id' => 'required|exists:services,id']);
        $service = Service::findOrFail($request->service_id);

        if (!$booking->bookingServices()->where('service_id', $service->id)->exists()) {
            BookingService::create([
                'booking_id' => $booking->id,
                'service_id' => $service->id,
                'price'      => $service->price,
            ]);
        }

        return redirect()->back()->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function removeService(Booking $booking, BookingService $bookingService)
    {
        $this->authorizeServiceEdit($booking);
        $bookingService->delete();
        return redirect()->back()->with('success', 'Layanan berhasil dihapus.');
    }

    public function addSparepart(Request $request, Booking $booking)
    {
        $this->authorizeServiceEdit($booking);

        $request->validate([
            'sparepart_id' => 'required|exists:spareparts,id',
            'qty'          => 'required|integer|min:1',
        ]);

        $sparepart = Sparepart::findOrFail($request->sparepart_id);

        if ($sparepart->stock < $request->qty) {
            return redirect()->back()->with('error', "Stok tidak mencukupi. Tersedia: {$sparepart->stock}");
        }

        $existing = $booking->transactionSpareparts()->where('sparepart_id', $sparepart->id)->first();
        if ($existing) {
            $newQty     = $existing->qty + $request->qty;
            $existing->update([
                'qty'      => $newQty,
                'subtotal' => $sparepart->price * $newQty,
            ]);
        } else {
            TransactionSparepart::create([
                'booking_id'   => $booking->id,
                'sparepart_id' => $sparepart->id,
                'qty'          => $request->qty,
                'price'        => $sparepart->price,
                'subtotal'     => $sparepart->price * $request->qty,
            ]);
        }

        $sparepart->decrement('stock', $request->qty);

        return redirect()->back()->with('success', 'Sparepart berhasil ditambahkan.');
    }

    public function removeSparepart(Booking $booking, TransactionSparepart $bookingSparepart)
    {
        $this->authorizeServiceEdit($booking);

        $sparepart = $bookingSparepart->sparepart;
        if ($sparepart) {
            $sparepart->increment('stock', (int) $bookingSparepart->qty);
        }
        
        $bookingSparepart->delete();

        return redirect()->back()->with('success', 'Sparepart berhasil dihapus.');
    }

    private function authorizeServiceEdit(Booking $booking): void
    {
        Gate::authorize('manage-data');
        $role = Auth::user()->role;
        if (!in_array($role, ['admin', 'mekanik'])) {
            abort(403, 'Only admin or mekanik can edit services and spareparts.');
        }

        if (in_array($booking->status, ['completed', 'cancelled', 'ready'])) {
            abort(403, 'Cannot edit services or spareparts for a booking in this status.');
        }
    }
}
