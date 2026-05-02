<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year   = $request->query('year', now()->year);
        $month  = $request->query('month', now()->month);
        $period = $request->query('period', 'monthly');

        $user = Auth::user();

        if ($user && $user->role === 'mekanik') {
            $stats = [
                'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
                'in_progress'        => Booking::where('status', 'in_progress')->count(),
                'completed_today'    => Booking::where('status', 'completed')->whereDate('updated_at', today())->count(),
                'ready_today'        => Booking::where('status', 'ready')->whereDate('updated_at', today())->count(),
            ];
        } else {
            $stats = [
                'total_bookings'      => Booking::count(),
                'pending_bookings'    => Booking::where('status', 'pending')->count(),
                'in_progress'         => Booking::where('status', 'in_progress')->count(),
                'completed_today'     => Booking::where('status', 'completed')->whereDate('updated_at', today())->count(),
                'total_revenue'       => Payment::where('payment_status', 'paid')->sum('amount_paid'),
                'revenue_today'       => Payment::where('payment_status', 'paid')->whereDate('payment_date', today())->sum('amount_paid'),
                'total_customers'     => User::where('role', 'customer')->count(),
                'unpaid_transactions' => Payment::where('payment_status', 'unpaid')->count(),
            ];
        }

        $recent_bookings_query = Booking::with(['user', 'vehicle'])
            ->orderByDesc('created_at');

        if ($user->role === 'mekanik') {
            $recent_bookings_query->whereIn('status', ['confirmed', 'in_progress', 'ready']);
        }

        $recent_bookings = $recent_bookings_query->limit(8)->get();

        $years = [];
        if ($user->role !== 'mekanik') {
            $years = Payment::selectRaw('YEAR(payment_date) as year')
                ->whereNotNull('payment_date')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->toArray();
            if (!in_array((int)$year, $years)) $years[] = (int)$year;
            if (!in_array(now()->year, $years)) $years[] = now()->year;
            sort($years);
        }

        $labels       = [];
        $revenue_data = [];

        if ($period === 'daily' && $user->role !== 'mekanik') {
            $daysInMonth   = Carbon::create($year, $month)->daysInMonth;
            $daily_revenue = Payment::where('payment_status', 'paid')
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', $month)
                ->selectRaw('DAY(payment_date) as day, SUM(amount_paid) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
                ->toArray();

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $labels[]       = $d;
                $revenue_data[] = $daily_revenue[$d] ?? 0;
            }
        } elseif ($user->role !== 'mekanik') {
            $monthly_revenue = Payment::where('payment_status', 'paid')
                ->whereYear('payment_date', $year)
                ->selectRaw('MONTH(payment_date) as month, SUM(amount_paid) as total')
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $monthLabels = collect(range(1, 12))->map(fn($m) => Carbon::create(null, $m)->translatedFormat('M'))->toArray();
            foreach ($monthLabels as $i => $m) {
                $labels[]       = $m;
                $revenue_data[] = $monthly_revenue[$i + 1] ?? 0;
            }
        }

        $monthOptions = [];
        if ($user->role !== 'mekanik') {
            $activeMonths = Payment::where('payment_status', 'paid')
                ->whereYear('payment_date', $year)
                ->selectRaw('MONTH(payment_date) as month')
                ->distinct()
                ->pluck('month')
                ->toArray();

            $monthOptions = collect(range(1, 12))->map(fn($m) => [
                'value'    => (string)$m,
                'label'    => Carbon::create(null, $m)->translatedFormat('F'),
                'disabled' => !in_array($m, $activeMonths)
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'labels'       => array_values($labels),
                'revenue_data' => array_values($revenue_data),
                'period'       => $period,
                'year'         => $year,
                'month'        => $month,
                'monthOptions' => $monthOptions,
                'chartTitle'   => ($period === 'daily' ? 'Pendapatan Harian' : 'Pendapatan Bulanan') . ' - ' . ($period === 'daily' ? Carbon::create($year, $month)->translatedFormat('F ') : '') . $year
            ]);
        }

        return view('admin.dashboard', compact(
            'stats', 'recent_bookings', 'revenue_data', 'labels', 
            'years', 'year', 'month', 'period', 'monthOptions'
        ));
    }
}
