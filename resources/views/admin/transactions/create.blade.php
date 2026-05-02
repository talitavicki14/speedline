@extends('layouts.admin')
@section('title','Buat Transaksi')
@section('page-title','Buat Transaksi')

@section('content')
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('admin.bookings.show', $booking) }}" class="text-slate-400 hover:text-slate-700">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Detail Booking
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <form id="transactionForm" action="{{ route('admin.transactions.store') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">

            {{-- Booking Summary --}}
            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden mb-5">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-sm text-slate-800">Detail Booking</h3>
                </div>
                <div class="p-6 grid grid-cols-2 gap-y-6 gap-x-8">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-1">Pelanggan</p>
                        <p class="font-semibold text-slate-900">{{ $booking->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-1">Kendaraan</p>
                        <p class="font-semibold text-slate-900">{{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-1">Tanggal Servis</p>
                        <p class="font-semibold text-slate-900">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-1">Nomor Plat</p>
                        <p class="font-semibold text-slate-900">{{ $booking->vehicle->license_plate }}</p>
                    </div>
                </div>
            </div>

            {{-- Assign Mechanic --}}
            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-sm text-slate-800">Pilih Mekanik</h3>
                </div>
                <div class="p-6">
                    <select name="mekanik_id" required
                            class="cs-replace w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none bg-white text-slate-700">
                        <option value="">Pilih mekanik...</option>
                        @foreach($mechanics as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    {{-- Cost Summary --}}
    <div class="space-y-4">
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-sm text-slate-800">Item Nota</h3>
                <span class="text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full uppercase">{{ $booking->bookingServices->count() + $booking->transactionSpareparts->count() }} Item</span>
            </div>
            
            {{-- Services --}}
            <div class="bg-slate-50/50 px-5 py-2 border-b border-slate-100/50">
                <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Layanan</span>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($booking->bookingServices as $bs)
                <div class="flex justify-between items-center px-5 py-3.5 hover:bg-slate-50/30 transition-colors">
                    <p class="text-sm font-medium text-slate-700">{{ $bs->service->service_name ?? '—' }}</p>
                    <span class="font-semibold text-sm text-slate-900">Rp {{ number_format($bs->price,0,',','.') }}</span>
                </div>
                @endforeach
            </div>

            {{-- Spareparts --}}
            @if($booking->transactionSpareparts->count())
            <div class="bg-slate-50/50 px-5 py-2 border-y border-slate-100/50">
                <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Sparepart</span>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($booking->transactionSpareparts as $bsp)
                <div class="flex justify-between items-center px-5 py-3.5 hover:bg-slate-50/30 transition-colors">
                    <div>
                        <p class="text-sm font-medium text-slate-700">{{ $bsp->sparepart->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400 tracking-tighter whitespace-nowrap">
                            <span class="font-semibold text-slate-600">{{ $bsp->qty }}</span> × Rp {{ number_format($bsp->price,0,',','.') }}
                        </p>
                    </div>
                    <span class="font-semibold text-sm text-slate-900">Rp {{ number_format($bsp->subtotal,0,',','.') }}</span>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Summary Totals --}}
            <div class="px-5 py-4 bg-slate-50/50 border-t border-slate-100 space-y-2">
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Total Layanan</span>
                    <span>Rp {{ number_format($booking->bookingServices->sum('price'),0,',','.') }}</span>
                </div>
                @if($booking->transactionSpareparts->count())
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Total Sparepart</span>
                    <span>Rp {{ number_format($booking->transactionSpareparts->sum('subtotal'),0,',','.') }}</span>
                </div>
                @endif
            </div>

            @php
                $grandTotal = $booking->bookingServices->sum('price') + $booking->transactionSpareparts->sum('subtotal');
            @endphp
            <div class="p-5 bg-slate-900 text-white">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-widest">Total Keseluruhan</span>
                    <span class="text-2xl font-bold tracking-tight">Rp {{ number_format($grandTotal,0,',','.') }}</span>
                </div>
                <button type="submit" form="transactionForm"
                        class="w-full flex items-center justify-center gap-2 bg-white text-slate-900 hover:bg-slate-100 font-bold text-sm py-4 rounded-xl transition-all shadow-lg shadow-white/5">
                    <i class="fas fa-receipt"></i> Buat Transaksi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/admin/transactions/create.js')
@endpush
