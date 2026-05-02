<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Sparepart;
use App\Models\Distributor;
use App\Traits\ReportHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\PurchasesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseReportController extends Controller
{
    use ReportHelpers;

    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->getReportDates($request);

        $query = Purchase::with(['sparepart', 'distributor'])
            ->whereBetween('purchase_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($request->distributor_id) {
            $query->where('distributor_id', $request->distributor_id);
        }

        if ($request->type) {
            $query->whereHas('sparepart', function($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        $purchases = (clone $query)->orderByDesc('purchase_date')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();
        
        $types = Sparepart::whereNotNull('type')->distinct()->pluck('type')->sort()->values();
        $distributors = Distributor::orderBy('name')->get();

        $totalQty = $query->sum('qty');
        $totalSpending = $query->sum('total_price');

        $topPurchased = (clone $query)
            ->select('sparepart_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('sparepart_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('sparepart')
            ->get();

        $topDistributors = (clone $query)
            ->select('distributor_id', DB::raw('SUM(total_price) as total_spent'))
            ->groupBy('distributor_id')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->with('distributor')
            ->get();

        $sixMonthTrend = $this->getSixMonthTrend();

        return view('admin.reports.purchases', compact(
            'purchases', 'totalQty', 'totalSpending', 'startDate', 'endDate', 
            'types', 'distributors', 'topPurchased', 'topDistributors', 'sixMonthTrend'
        ));
    }

    public function exportPdf(Request $request)
    {
        [$startDate, $endDate] = $this->getReportDates($request);

        $query = Purchase::with(['sparepart', 'distributor'])
            ->whereBetween('purchase_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            
        if ($request->distributor_id) {
            $query->where('distributor_id', $request->distributor_id);
        }
        if ($request->type_filter) {
            $query->whereHas('sparepart', function($q) use ($request) {
                $q->where('type', $request->type_filter);
            });
        }
        
        $topPurchased = (clone $query)
            ->select('sparepart_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('sparepart_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('sparepart')
            ->get();

        $topDistributors = (clone $query)
            ->select('distributor_id', DB::raw('SUM(total_price) as total_spent'))
            ->groupBy('distributor_id')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->with('distributor')
            ->get();

        $sixMonthTrend = $this->getSixMonthTrend();
        $chartUrl = $this->generateChartUrl($sixMonthTrend);
        $chartBase64 = $this->urlToBase64($chartUrl);

        $totalQty = $query->sum('qty');
        $totalSpending = $query->sum('total_price');

        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'purchases' => $query->orderByDesc('purchase_date')->get(),
            'totalQty' => $totalQty,
            'totalSpending' => $totalSpending,
            'topPurchased' => $topPurchased,
            'topDistributors' => $topDistributors,
            'chartBase64' => $chartBase64
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.purchases', $data)->setPaper('a4', 'portrait');
        return $pdf->download("Laporan_Pembelian_{$startDate}_{$endDate}.pdf");
    }

    public function exportExcel(Request $request)
    {
        [$startDate, $endDate] = $this->getReportDates($request);

        $query = Purchase::with(['sparepart', 'distributor'])
            ->whereBetween('purchase_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            
        if ($request->distributor_id) {
            $query->where('distributor_id', $request->distributor_id);
        }
        if ($request->type_filter) {
            $query->whereHas('sparepart', function($q) use ($request) {
                $q->where('type', $request->type_filter);
            });
        }

        $purchases = $query->orderByDesc('purchase_date')->get();

        return Excel::download(new PurchasesExport($purchases, $startDate, $endDate), "Laporan_Pembelian_{$startDate}_{$endDate}.xlsx");
    }

    private function getSixMonthTrend()
    {
        $trendQuery = Purchase::query()
            ->where('purchase_date', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw('DATE_FORMAT(purchase_date, "%Y-%m") as month'),
                DB::raw('SUM(total_price) as total_amount'),
                DB::raw('SUM(qty) as total_qty')
            )
            ->groupBy('month')
            ->get();

        $trend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $found = $trendQuery->where('month', $monthKey)->first();
            
            $trend->push((object)[
                'month_label' => $month->translatedFormat('M Y'),
                'total' => $found ? (float)$found->total_amount : 0,
                'qty' => $found ? (int)$found->total_qty : 0
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
                'datasets' => [[
                    'label' => 'Pengeluaran',
                    'data' => $trend->pluck('total')->toArray(),
                    'backgroundColor' => '#0f172a',
                    'barThickness' => 30,
                ]]
            ],
            'options' => [
                'legend' => ['display' => false],
                'scales' => [
                    'yAxes' => [['gridLines' => ['drawBorder' => false, 'color' => 'rgba(0,0,0,0.05)'], 'ticks' => ['beginAtZero' => true, 'fontSize' => 11]]],
                    'xAxes' => [['gridLines' => ['display' => false], 'ticks' => ['fontSize' => 11]]]
                ],
                'plugins' => ['datalabels' => ['display' => false]]
            ]
        ];

        return 'https://quickchart.io/chart?w=800&h=350&c=' . urlencode(json_encode($chartConfig));
    }
}
