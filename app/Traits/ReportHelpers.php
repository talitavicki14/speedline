<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;


trait ReportHelpers
{
    protected function getReportDates($request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        if ($request->period === 'this_month') {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        } elseif ($request->period === 'last_month') {
            $startDate = now()->subMonth()->startOfMonth()->toDateString();
            $endDate = now()->subMonth()->endOfMonth()->toDateString();
        } elseif ($request->period === 'this_year') {
            $startDate = now()->startOfYear()->toDateString();
            $endDate = now()->endOfYear()->toDateString();
        }

        return [$startDate, $endDate];
    }

    protected function urlToBase64($url)
    {
        try {
            $response = Http::withoutVerifying()->timeout(10)->get($url);
            
            if ($response->successful()) {
                return 'data:image/png;base64,' . base64_encode($response->body());
            }
            
            return null;
        } catch (\Exception $e) { 
            return null; 
        }
    }
}
