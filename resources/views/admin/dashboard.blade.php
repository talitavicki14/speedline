@extends('layouts.admin')
@section('title','Dashboard')
@section('page-title','Dashboard')

@section('content')
{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
    $userRole = auth()->user()->role;
    if ($userRole === 'mekanik') {
        $cards = [
            ['Dikonfirmasi', $stats['confirmed_bookings'], 'fas fa-check-circle', 'blue'],
            ['Dikerjakan', $stats['in_progress'], 'fas fa-wrench', 'amber'],
            ['Siap Hari Ini', $stats['ready_today'], 'fas fa-car-side', 'emerald'],
            ['Selesai Hari Ini', $stats['completed_today'], 'fas fa-calendar-check', 'slate'],
        ];
    } else {
        $cards = [
            ['Total Booking', $stats['total_bookings'], 'fas fa-calendar-check', 'slate'],
            ['Menunggu', $stats['pending_bookings'], 'fas fa-hourglass-half', 'amber'],
            ['Dikerjakan', $stats['in_progress'], 'fas fa-wrench', 'blue'],
            ['Pendapatan Hari Ini', 'Rp '.number_format($stats['revenue_today'],0,',','.'), 'fas fa-coins', 'emerald'],
        ];
    }
    @endphp
    @foreach($cards as [$label,$value,$icon,$color])
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm">
        <div class="flex items-start justify-between mb-3">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ $label }}</p>
            <div class="w-8 h-8 rounded-lg bg-{{ $color }}-50 flex items-center justify-center flex-shrink-0">
                <i class="{{ $icon }} text-{{ $color }}-500 text-xs"></i>
            </div>
        </div>
        <p class="font-display font-bold text-2xl text-slate-900">{{ $value }}</p>
    </div>
    @endforeach
</div>

@if(auth()->user()->role !== 'mekanik')
<div class="grid lg:grid-cols-3 gap-5">
    {{-- Revenue Chart --}}
    <div class="lg:col-span-2 bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h3 id="chartTitle" class="font-semibold text-sm text-slate-800">
                    Pendapatan {{ $period === 'daily' ? 'Harian' : 'Bulanan' }} - {{ $period === 'daily' ? \Carbon\Carbon::create($year, $month)->translatedFormat('F ') : '' }}{{ $year }}
                </h3>
            </div>
            <div class="flex items-center gap-2" id="chartFilters">
                <div data-custom-select
                     class="w-32"
                     data-name="period"
                     data-value="{{ $period }}"
                     data-hide-placeholder="true"
                     data-options='[{"value":"monthly","label":"Bulanan"},{"value":"daily","label":"Harian"}]'></div>
                
                <div data-custom-select
                     class="w-24"
                     data-name="year"
                     data-value="{{ $year }}"
                     data-hide-placeholder="true"
                     data-options='@json(collect($years)->map(fn($y) => ["value"=>$y,"label"=>$y]))'></div>

                <div data-custom-select
                     id="monthSelectorWrapper"
                     class="w-32 {{ $period !== 'daily' ? 'hidden' : '' }}"
                     data-name="month"
                     data-value="{{ $month }}"
                     data-hide-placeholder="true"
                     data-options='@json($monthOptions)'></div>
            </div>
        </div>
        <div class="px-6 py-8" style="height:320px">
            @if(collect($revenue_data)->sum() > 0)
                <canvas id="revenueChart" 
                        data-period="{{ $period }}"
                        data-year="{{ $year }}"
                        data-month="{{ $month }}"
                        data-revenue='@json(array_values($revenue_data))'
                        data-labels='@json(array_values($labels))'></canvas>
            @else
                <div class="h-full flex flex-col items-center justify-center text-slate-300 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                    <i class="fas fa-chart-bar text-4xl mb-3 opacity-20"></i>
                    <p class="text-sm font-medium">Tidak ada data pendapatan untuk periode ini</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-sm text-slate-800">Ringkasan Statistik</h3>
        </div>
        <div class="divide-y divide-slate-100">
            <div class="flex items-center justify-between px-6 py-4">
                <span class="text-sm text-slate-500">Total Pelanggan</span>
                <span class="font-semibold text-slate-900">{{ $stats['total_customers'] }}</span>
            </div>
            <div class="flex items-center justify-between px-6 py-4">
                <span class="text-sm text-slate-500">Selesai Hari Ini</span>
                <span class="font-semibold text-emerald-600">{{ $stats['completed_today'] }}</span>
            </div>
            <div class="flex items-center justify-between px-6 py-4">
                <span class="text-sm text-slate-500">Total Pendapatan</span>
                <span class="font-semibold text-slate-900">Rp {{ number_format($stats['total_revenue'],0,',','.') }}</span>
            </div>
            <div class="flex items-center justify-between px-6 py-4">
                <span class="text-sm text-slate-500">Nota Belum Lunas</span>
                <span class="font-semibold text-amber-600">{{ $stats['unpaid_transactions'] }}</span>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-100">
            <a href="{{ route('admin.bookings.index') }}"
               class="w-full block text-center bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">
                Lihat Semua Booking
            </a>
        </div>
    </div>
</div>
@endif

{{-- Recent Bookings --}}
<div class="mt-5 bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-sm text-slate-800">Booking Terbaru</h3>
        <div class="flex items-center gap-2">
            <input id="dashboardSearch" type="text" placeholder="Cari pelanggan..."
                class="border border-slate-200 focus:border-slate-400 rounded-lg px-3 py-2 text-sm outline-none w-40 transition-colors placeholder-slate-300 text-slate-700">
             @php
                $labels_indo = [
                    'pending'     => 'Menunggu',
                    'confirmed'   => 'Dikonfirmasi',
                    'in_progress' => 'Dikerjakan',
                    'ready'       => 'Siap Diambil',
                    'completed'   => 'Selesai',
                    'cancelled'   => 'Dibatalkan',
                ];
                $status_options = collect(['pending','confirmed','in_progress', 'ready', 'completed','cancelled'])->map(fn($s) => [
                    'value' => $s,
                    'label' => $labels_indo[$s] ?? ucfirst(str_replace('_',' ',$s))
                ]);
            @endphp
            <div data-custom-select
                 id="dashboardStatus"
                 data-name="status"
                 data-placeholder="Semua Status"
                 data-value=""
                 data-options='@json($status_options)'
                 class="w-40"></div>
            <a href="{{ route('admin.bookings.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline">Lihat semua</a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Pelanggan</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Kendaraan</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Tanggal</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody id="recentBookingsBody" class="divide-y divide-slate-100">
                @foreach($recent_bookings as $b)
                <tr class="hover:bg-slate-50 transition-colors"
                    data-customer="{{ strtolower($b->user->name ?? '') }}"
                    data-status="{{ $b->status }}">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center text-xs font-semibold text-slate-600 flex-shrink-0">
                                {{ strtoupper(substr($b->user->name??'?',0,1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-sm text-slate-800">{{ $b->user->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $b->user->phone ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $b->vehicle ? $b->vehicle->brand.' '.$b->vehicle->model : '—' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ \Carbon\Carbon::parse($b->booking_date)->format('d M Y') }}</td>
                    <td class="px-6 py-4">
                        <x-badges.status :status="$b->status" />
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.bookings.show', $b) }}"
                           class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">
                            Lihat
                        </a>
                    </td>
                </tr>
                @endforeach
                
                <tr data-empty class="{{ $recent_bookings->isEmpty() ? '' : 'hidden' }}">
                    <td colspan="5" class="px-6 py-16">
                        <div class="flex flex-col items-center justify-center text-center">
                            <i class="fas fa-calendar-times text-4xl mb-4 text-slate-200"></i>
                            <p class="text-sm text-slate-400 font-medium tracking-wide">Tidak ada booking ditemukan</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/pages/admin/dashboard/index.js', 'resources/js/pages/admin/dashboard/filter.js'])
@endpush
