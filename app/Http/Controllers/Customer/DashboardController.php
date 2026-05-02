<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $bookings = $user->bookings()->with(['vehicle', 'services', 'transaction.payment'])
            ->orderByDesc('created_at')->limit(5)->get();
        $vehicles = $user->vehicles;
        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'active' => $user->bookings()->whereIn('status', ['pending', 'confirmed', 'in_progress'])->count(),
            'completed' => $user->bookings()->where('status', 'completed')->count(),
        ];
        return view('customer.dashboard', compact('bookings', 'vehicles', 'stats'));
    }
}
