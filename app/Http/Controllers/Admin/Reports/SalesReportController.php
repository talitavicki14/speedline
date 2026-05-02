<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\BookingService;
use App\Models\TransactionSparepart;
use App\Models\Sparepart;
use App\Traits\ReportHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\SalesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
{
    use ReportHelpers;

    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->getReportDates($request);

        $sparepartQuery = TransactionSparepart::query()
            ->select('transaction_spareparts.*', 
                DB::raw("COALESCE(bookings.updated_at, transactions.created_at) as sale_date"),
                DB::raw("COALESCE(users.name, 'Pelanggan Umum (Retail)') as customer_name")
            )
            ->join('transactions', 'transaction_spareparts.transaction_id', '=', 'transactions.id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->leftJoin('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->join('spareparts', 'transaction_spareparts.sparepart_id', '=', 'spareparts.id')
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
            });

        $serviceQuery = BookingService::query()
            ->select('booking_services.*', 
                'bookings.updated_at as sale_date',
                'users.name as customer_name'
            )
            ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
            ->join('transactions', 'bookings.id', '=', 'transactions.booking_id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->where('bookings.status', 'completed')
            ->whereBetween('bookings.updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($request->type) {
            $sparepartQuery->where('spareparts.type', $request->type);
        }
        if ($request->brand) {
            $sparepartQuery->where('spareparts.brand', $request->brand);
        }

        $sparepartSales = $sparepartQuery->orderByDesc('transactions.created_at')->get();
        $serviceSales = $serviceQuery->orderByDesc('bookings.updated_at')->get();

        $totalQty = $sparepartSales->sum('qty');
        $totalSparepartSales = $sparepartSales->sum('subtotal');
        $totalServiceSales = $serviceSales->sum('price');
        $totalSales = $totalSparepartSales + $totalServiceSales;

        $types = Sparepart::whereNotNull('type')->distinct()->pluck('type')->sort()->values();
        $brands = Sparepart::whereNotNull('brand')->distinct()->pluck('brand')->sort()->values();

        $topSpareparts = TransactionSparepart::query()
            ->join('transactions', 'transaction_spareparts.transaction_id', '=', 'transactions.id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('sparepart_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('sparepart_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('sparepart')
            ->get();

        $topServices = BookingService::query()
            ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
            ->join('transactions', 'bookings.id', '=', 'transactions.booking_id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->where('bookings.status', 'completed')
            ->whereBetween('bookings.updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('service_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('service_id')
            ->orderByDesc('total_count')
            ->limit(5)
            ->with('service')
            ->get();

        $totalServicesCount = $serviceSales->count();
        $sixMonthTrend = $this->getSixMonthTrend();

        return view('admin.reports.sales', compact(
            'sparepartSales', 'serviceSales', 'totalQty', 'totalServicesCount', 'totalSales', 
            'totalSparepartSales', 'totalServiceSales',
            'startDate', 'endDate', 'types', 'brands', 'topSpareparts', 'topServices', 'sixMonthTrend'
        ));
    }

    public function exportPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getReportDates($request);

        $sparepartSales = TransactionSparepart::query()
            ->select('transaction_spareparts.*', 
                DB::raw("COALESCE(bookings.updated_at, transactions.created_at) as sale_date"),
                DB::raw("COALESCE(users.name, 'Pelanggan Umum (Retail)') as customer_name")
            )
            ->join('transactions', 'transaction_spareparts.transaction_id', '=', 'transactions.id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->leftJoin('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->join('spareparts', 'transaction_spareparts.sparepart_id', '=', 'spareparts.id')
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
            ->when($request->type_filter, fn($q) => $q->where('spareparts.type', $request->type_filter))
            ->get();

        $serviceSales = BookingService::query()
            ->select('booking_services.*', 
                'bookings.updated_at as sale_date',
                'users.name as customer_name'
            )
            ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
            ->join('transactions', 'bookings.id', '=', 'transactions.booking_id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->where('bookings.status', 'completed')
            ->whereBetween('bookings.updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        $topSpareparts = TransactionSparepart::query()
            ->join('transactions', 'transaction_spareparts.transaction_id', '=', 'transactions.id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('sparepart_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('sparepart_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('sparepart')
            ->get();

        $topServices = BookingService::query()
            ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
            ->join('transactions', 'bookings.id', '=', 'transactions.booking_id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->where('bookings.status', 'completed')
            ->whereBetween('bookings.updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select('service_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('service_id')
            ->orderByDesc('total_count')
            ->limit(5)
            ->with('service')
            ->get();

        $sixMonthTrend = $this->getSixMonthTrend();
        $chartBase64 = $this->urlToBase64($this->generateChartUrl($sixMonthTrend));

        $totalSparepartSales = $sparepartSales->sum('subtotal');
        $totalServiceSales = $serviceSales->sum('price');

        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sparepartSales' => $sparepartSales,
            'serviceSales' => $serviceSales,
            'totalQty' => $sparepartSales->sum('qty'),
            'totalSparepartSales' => $totalSparepartSales,
            'totalServiceSales' => $totalServiceSales,
            'totalSales' => $totalSparepartSales + $totalServiceSales,
            'topSpareparts' => $topSpareparts,
            'topServices' => $topServices,
            'chartBase64' => $chartBase64
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.sales', $data)->setPaper('a4', 'portrait');
        return $pdf->download("Laporan_Penjualan_{$startDate}_{$endDate}.pdf");
    }

    public function exportExcel(Request $request)
    {
        [$startDate, $endDate] = $this->getReportDates($request);

        $sparepartSales = TransactionSparepart::query()
            ->select('transaction_spareparts.*', 
                DB::raw("COALESCE(bookings.updated_at, transactions.created_at) as sale_date"),
                DB::raw("COALESCE(users.name, 'Pelanggan Umum (Retail)') as customer_name")
            )
            ->join('transactions', 'transaction_spareparts.transaction_id', '=', 'transactions.id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->leftJoin('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->join('spareparts', 'transaction_spareparts.sparepart_id', '=', 'spareparts.id')
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
            ->when($request->type_filter, fn($q) => $q->where('spareparts.type', $request->type_filter))
            ->get();

        $serviceSales = BookingService::query()
            ->select('booking_services.*', 
                'bookings.updated_at as sale_date',
                'users.name as customer_name'
            )
            ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->join('transactions', 'bookings.id', '=', 'transactions.booking_id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->where('bookings.status', 'completed')
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->whereBetween('bookings.updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        return Excel::download(new SalesExport($sparepartSales, $serviceSales, $startDate, $endDate), "Laporan_Penjualan_{$startDate}_{$endDate}.xlsx");
    }

    private function getSixMonthTrend()
    {
        $sparepartTrend = TransactionSparepart::query()
            ->join('transactions', 'transaction_spareparts.transaction_id', '=', 'transactions.id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->where('transactions.created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw('DATE_FORMAT(transactions.created_at, "%Y-%m") as month'),
                DB::raw('SUM(transaction_spareparts.qty) as total_qty')
            )
            ->groupBy('month')
            ->get();

        $serviceTrend = BookingService::query()
            ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
            ->join('transactions', 'bookings.id', '=', 'transactions.booking_id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->whereIn('payments.payment_status', ['paid', 'partial'])
            ->where('bookings.updated_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw('DATE_FORMAT(bookings.updated_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total_services')
            )
            ->groupBy('month')
            ->get();

        $trend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            
            $foundSparepart = $sparepartTrend->where('month', $monthKey)->first();
            $foundService = $serviceTrend->where('month', $monthKey)->first();
            
            $trend->push((object)[
                'month_label' => $month->translatedFormat('M Y'),
                'spareparts' => $foundSparepart ? (int)$foundSparepart->total_qty : 0,
                'services' => $foundService ? (int)$foundService->total_services : 0
            ]);
        }
        return $trend;
    }

    private function generateChartUrl($trend)
    {
        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $trend->pluck('month_label')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Sparepart',
                        'data' => $trend->pluck('spareparts')->toArray(),
                        'backgroundColor' => '#0f172a',
                        'barThickness' => 30,
                    ],
                    [
                        'label' => 'Layanan',
                        'data' => $trend->pluck('services')->toArray(),
                        'backgroundColor' => '#94a3b8',
                        'barThickness' => 30,
                    ]
                ]
            ],
            'options' => [
                'legend' => ['position' => 'bottom', 'labels' => ['fontSize' => 11]],
                'scales' => [
                    'yAxes' => [['stacked' => true, 'gridLines' => ['drawBorder' => false, 'color' => 'rgba(0,0,0,0.05)'], 'ticks' => ['beginAtZero' => true, 'fontSize' => 11]]],
                    'xAxes' => [['stacked' => true, 'gridLines' => ['display' => false], 'ticks' => ['fontSize' => 11]]]
                ],
                'plugins' => ['datalabels' => ['display' => false]]
            ]
        ];

        return 'https://quickchart.io/chart?w=800&h=350&c=' . urlencode(json_encode($chartConfig));
    }
}
