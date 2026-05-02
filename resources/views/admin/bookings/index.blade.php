@extends('layouts.admin')
@section('title','Booking')
@section('page-title','Daftar Booking')

@section('content')
<div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-sm text-slate-800">Semua Booking</h3>
        <form method="GET" class="flex items-center gap-2 flex-wrap" data-auto-filter>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pelanggan..."
                   class="border border-slate-200 focus:border-slate-400 rounded-lg px-3 py-2 text-sm outline-none w-44 transition-colors placeholder-slate-300 text-slate-700">

            <div data-datepicker
                 data-name="date"
                 data-value="{{ request('date') }}"
                 data-placeholder="Pilih tanggal"
                 data-highlight-today="true"
                 class="w-44"></div>

            @php
                $labels_indo = [
                    'pending'     => 'Menunggu',
                    'confirmed'   => 'Dikonfirmasi',
                    'in_progress' => 'Dikerjakan',
                    'ready'       => 'Siap Diambil',
                    'completed'   => 'Selesai',
                    'cancelled'   => 'Dibatalkan',
                ];
                $status_options = collect(["pending","confirmed","in_progress","ready","completed","cancelled"])
                    ->map(fn($s) => ["value" => $s, "label" => $labels_indo[$s] ?? ucfirst(str_replace("_", " ", $s))])
                    ->values();
            @endphp
            <div data-custom-select
                 data-name="status"
                 data-placeholder="Semua Status"
                 data-value="{{ request('status') }}"
                 data-options='@json($status_options)'
                 class="w-40"></div>

            <div id="clear-container">
                @if(request()->hasAny(['search','status','date']))
                <a href="{{ route('admin.bookings.index') }}"
                class="text-sm text-slate-500 hover:text-slate-800 px-3 py-2 border border-slate-200 rounded-lg transition-colors">Hapus Filter</a>
                @endif
            </div>
        </form>
    </div>
    <div id="filter-container">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Pelanggan</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Kendaraan</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Tanggal & Waktu</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Layanan</th>
                    <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($bookings as $b)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-medium text-sm text-slate-800">{{ $b->user->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $b->user->email ?? '' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-slate-700">{{ $b->vehicle ? $b->vehicle->brand.' '.$b->vehicle->model : '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $b->vehicle->license_plate ?? '' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-slate-700">{{ \Carbon\Carbon::parse($b->booking_date)->translatedFormat('d M Y') }}</p>
                        <p class="text-xs text-slate-400">{{ substr($b->booking_time,0,5) }} WIB</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        {{ $b->services->count() }} layanan
                    </td>
                    <td class="px-6 py-4">
                        <x-badges.status :status="$b->status" />
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.bookings.show', $b) }}"
                           class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">
                            Kelola
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-20">
                        <div class="flex flex-col items-center justify-center text-center">
                            <i class="fas fa-calendar-times text-5xl mb-4 text-slate-200"></i>
                            <p class="text-sm text-slate-400 font-medium tracking-wide">Tidak ada booking ditemukan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination footer --}}
    <x-ui.table-footer :paginator="$bookings" />
    </div>
</div>
@endsection
