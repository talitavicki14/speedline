<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\BookingService;
use App\Models\TransactionSparepart;
use App\Traits\ReportHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\FinanceExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class FinanceReportController extends Controller
{
    use ReportHelpers;

    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->getReportDates($request);
        $stats = $this->getStats($startDate, $endDate);
        
        $payments = $this->getPaymentsBaseQuery($startDate, $endDate)
            ->orderByDesc('bookings.updated_at')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return view('admin.reports.finance', compact('payments', 'stats', 'startDate', 'endDate'));
    }

    public function exportExcel(Request $request)
    {
        [$startDate, $endDate] = $this->getReportDates($request);

        $payments = Payment::with(['transaction.booking.user', 'transaction.booking.vehicle'])
            ->whereIn('payment_status', ['paid', 'partial'])
            ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                $q->where(function($q1) use ($startDate, $endDate) {
                    $q1->whereNotNull('booking_id')
                       ->whereHas('booking', function($q2) use ($startDate, $endDate) {
                           $q2->where('status', 'completed')
                              ->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                       });
                })->orWhere(function($q1) use ($startDate, $endDate) {
                    $q1->whereNull('booking_id')
                       ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                });
            })
            ->select('payments.*')
            ->addSelect(DB::raw('(SELECT name FROM users WHERE id = (SELECT user_id FROM bookings WHERE id = (SELECT booking_id FROM transactions WHERE id = payments.transaction_id))) as customer_name'))
            ->addSelect(DB::raw('(SELECT model FROM vehicles WHERE id = (SELECT vehicle_id FROM bookings WHERE id = (SELECT booking_id FROM transactions WHERE id = payments.transaction_id))) as vehicle_model'))
            ->addSelect(DB::raw('(SELECT license_plate FROM vehicles WHERE id = (SELECT vehicle_id FROM bookings WHERE id = (SELECT booking_id FROM transactions WHERE id = payments.transaction_id))) as license_plate'))
            ->orderBy(DB::raw('COALESCE((SELECT updated_at FROM bookings WHERE id = (SELECT booking_id FROM transactions WHERE id = payments.transaction_id)), payments.created_at)'), 'desc')
            ->get();

        return Excel::download(new FinanceExport($payments, $startDate, $endDate), "Laporan_Keuangan_{$startDate}_{$endDate}.xlsx");
    }

    public function exportPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getReportDates($request);
        
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'stats' => $this->getStats($startDate, $endDate),
            'payments' => $this->getPaymentsBaseQuery($startDate, $endDate)->orderByDesc('bookings.updated_at')->get(),
        ];

        $data['lineChartBase64'] = $this->urlToBase64($this->generateChartUrl($data['stats']['six_month_revenue']));
        $data['pieChartBase64'] = $this->urlToBase64($this->generatePieChartUrl($data['stats']['top_services']));
        $data['sparepartChartBase64'] = $this->urlToBase64($this->generateSparepartChartUrl($data['stats']['top_spareparts']));

        $pdf = Pdf::loadView('admin.reports.pdf.finance', $data)->setPaper('a4', 'portrait');
        return $pdf->download("Laporan_Keuangan_{$startDate}_{$endDate}.pdf");
    }

    private function getPaymentsBaseQuery($startDate, $endDate)
    {
        return Payment::query()
            ->join('transactions', 'payments.transaction_id', '=', 'transactions.id')
            ->leftJoin('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->where(function($q1) use ($startDate, $endDate) {
                    $q1->whereNotNull('transactions.booking_id')
                       ->where('bookings.status', 'completed')
                       ->whereBetween('bookings.updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                })->orWhere(function($q2) use ($startDate, $endDate) {
                    $q2->whereNull('transactions.booking_id')
                       ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                });
            })
            ->select(
                'payments.*',
                DB::raw("COALESCE(users.name, 'Pelanggan Umum (Retail)') as customer_name"),
                'vehicles.model as vehicle_model',
                'vehicles.license_plate',
                DB::raw("COALESCE(bookings.updated_at, transactions.created_at) as completed_at")
            );
    }

    private function getStats($startDate, $endDate)
    {
        $totalSparepartsSold = $this->applyBookingConstraints(new TransactionSparepart, $startDate, $endDate)->sum('qty');
        
        $topSpareparts = $this->applyBookingConstraints(TransactionSparepart::with(['sparepart']), $startDate, $endDate)
            ->select('sparepart_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('sparepart_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $totalCarsServiced = $this->applyBookingConstraints(new Booking, $startDate, $endDate)
            ->distinct('vehicle_id')
            ->count('vehicle_id');

        $totalServicesCount = $this->applyBookingConstraints(new BookingService, $startDate, $endDate)->count();

        $topServices = $this->applyBookingConstraints(BookingService::with(['service']), $startDate, $endDate)
            ->select('service_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('service_id')
            ->orderByDesc('total_count')
            ->limit(5)
            ->get();

        $totalSparepartSales = $this->applyBookingConstraints(new TransactionSparepart, $startDate, $endDate)->sum(DB::raw('qty * price'));

        $totalSparepartProfit = $this->applyBookingConstraints(TransactionSparepart::query(), $startDate, $endDate)
            ->join('spareparts', 'transaction_spareparts.sparepart_id', '=', 'spareparts.id')
            ->sum(DB::raw('qty * (transaction_spareparts.price - spareparts.purchase_price)'));

        $totalServiceProfit = $this->applyBookingConstraints(new BookingService, $startDate, $endDate)->sum('price');
        $totalProfit = $totalSparepartProfit + $totalServiceProfit;

        $totalRevenue = Payment::whereIn('payment_status', ['paid', 'partial'])
            ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                $q->where(function($q1) use ($startDate, $endDate) {
                    $q1->whereNotNull('booking_id')
                       ->whereHas('booking', function($q2) use ($startDate, $endDate) {
                           $q2->where('status', 'completed')
                              ->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                       });
                })->orWhere(function($q3) use ($startDate, $endDate) {
                    $q3->whereNull('booking_id')
                       ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                });
            })
            ->sum('amount_paid');

        $dailyRevenue = Payment::query()
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->whereBetween('payments.payment_date', [$startDate, $endDate])
            ->select(DB::raw('DATE(payments.payment_date) as date'), DB::raw('SUM(payments.amount_paid) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $sixMonthRevenue = $this->getSixMonthTrend();

        return [
            'total_spareparts_sold' => $totalSparepartsSold,
            'total_sparepart_sales' => $totalSparepartSales,
            'top_spareparts' => $topSpareparts,
            'total_cars_serviced' => $totalCarsServiced,
            'total_services_count' => $totalServicesCount,
            'top_services' => $topServices,
            'total_revenue' => $totalRevenue,
            'total_profit' => $totalProfit,
            'daily_revenue' => $dailyRevenue,
            'six_month_revenue' => $sixMonthRevenue
        ];
    }

    private function applyBookingConstraints($query, $startDate, $endDate)
    {
        $model = ($query instanceof Builder) ? $query->getModel() : $query;

        if ($model instanceof BookingService) {
            return $query->whereHas('booking', function($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                  ->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })->whereHas('booking.transaction.payment', function($q) {
                $q->whereIn('payment_status', ['paid', 'partial']);
            });
        }

        return $query->whereHas('transaction', function($q) use ($startDate, $endDate) {
            $q->whereHas('payment', function($qp) {
                $qp->whereIn('payment_status', ['paid', 'partial']);
            });

            $q->where(function($q1) use ($startDate, $endDate) {
                $q1->where(function($q_booking) use ($startDate, $endDate) {
                    $q_booking->whereNotNull('booking_id')
                       ->whereHas('booking', function($qb) use ($startDate, $endDate) {
                           $qb->where('status', 'completed')
                             ->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                       });
                })->orWhere(function($q_retail) use ($startDate, $endDate) {
                    $q_retail->whereNull('booking_id')
                       ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                });
            });
        });
    }

    private function getSixMonthTrend()
    {
        $sixMonthQuery = Payment::query()
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->where('payments.payment_date', '>=', now()->subMonths(5)->startOfMonth()->toDateString())
            ->select(DB::raw('DATE_FORMAT(payments.payment_date, "%Y-%m") as month'), DB::raw('SUM(payments.amount_paid) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $trend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $found = $sixMonthQuery->where('month', $monthKey)->first();
            
            $trend->push((object)[
                'month_label' => $month->translatedFormat('M Y'),
                'total' => $found ? $found->total : 0
            ]);
        }
        return $trend;
    }

    private function generatePieChartUrl($topServices)
    {
        $total = $topServices->sum('total_count');
        $labels = $topServices->map(function($item) use ($total) {
            $name = $item->service->service_name ?? 'Layanan';
            $name = strlen($name) > 15 ? substr($name, 0, 15) . '...' : $name;
            $percent = $total > 0 ? round(($item->total_count / $total) * 100) : 0;
            return "$name ($percent%)";
        })->toArray();
        
        $chartConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $topServices->pluck('total_count')->toArray(), 
                    'backgroundColor' => ['#0f172a', '#1e293b', '#334155', '#475569', '#64748b'], 
                    'borderWidth' => 0
                ]]
            ],
            'options' => [
                'cutoutPercentage' => 65,
                'legend' => ['position' => 'bottom', 'labels' => ['fontSize' => 11, 'boxWidth' => 8]],
                'plugins' => ['datalabels' => ['display' => false]]
            ]
        ];

        return 'https://quickchart.io/chart?w=400&h=300&c=' . urlencode(json_encode($chartConfig));
    }

    private function generateChartUrl($sixMonthRevenue)
    {
        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $sixMonthRevenue->pluck('month_label')->toArray(),
                'datasets' => [['label' => 'Pendapatan', 'data' => $sixMonthRevenue->pluck('total')->toArray(), 'backgroundColor' => 'rgba(15, 23, 42, 0.85)', 'borderColor' => '#0f172a', 'borderWidth' => 1, 'barThickness' => 30]]
            ],
            'options' => [
                'legend' => ['display' => false],
                'scales' => [
                    'yAxes' => [['gridLines' => ['drawBorder' => false, 'color' => 'rgba(0,0,0,0.05)'], 'ticks' => ['beginAtZero' => true, 'fontSize' => 11, 'fontColor' => '#334155']]],
                    'xAxes' => [['gridLines' => ['display' => false], 'ticks' => ['fontSize' => 11, 'fontColor' => '#334155']]]
                ],
                'plugins' => ['datalabels' => ['display' => false]]
            ]
        ];

        return 'https://quickchart.io/chart?w=800&h=450&c=' . urlencode(json_encode($chartConfig));
    }
    private function generateSparepartChartUrl($topSpareparts)
    {
        $total = $topSpareparts->sum('total_qty');
        $labels = $topSpareparts->map(function($item) use ($total) {
            $name = $item->sparepart->name ?? 'Sparepart';
            $name = strlen($name) > 15 ? substr($name, 0, 15) . '...' : $name;
            $percent = $total > 0 ? round(($item->total_qty / $total) * 100) : 0;
            return "$name ($percent%)";
        })->toArray();

        $chartConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $topSpareparts->pluck('total_qty')->toArray(),
                    'backgroundColor' => ['#0f172a', '#1e293b', '#334155', '#475569', '#64748b'],
                    'borderWidth' => 0
                ]]
            ],
            'options' => [
                'cutoutPercentage' => 65,
                'legend' => ['position' => 'bottom', 'labels' => ['fontSize' => 11, 'boxWidth' => 8]],
                'plugins' => ['datalabels' => ['display' => false]]
            ]
        ];

        return 'https://quickchart.io/chart?w=400&h=300&c=' . urlencode(json_encode($chartConfig));
    }
}
