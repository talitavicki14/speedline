@extends('layouts.customer')
@section('title','Detail Booking')
@section('content')
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('customer.bookings.index') }}" class="text-slate-400 hover:text-slate-700"><i class="fas fa-arrow-left mr-1"></i> Booking Saya</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-500">Booking #{{ $booking->id }}</span>
</div>

<div class="max-w-2xl space-y-5">
    {{-- Status header --}}
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm px-6 py-5 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-1">Booking #{{ $booking->id }}</p>
            <p class="font-display font-bold text-2xl text-slate-900">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }} · {{ substr($booking->booking_time,0,5) }} WIB</p>
        </div>
        <x-badges.status :status="$booking->status" />
    </div>

    {{-- Vehicle & complaint --}}
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6 grid grid-cols-2 gap-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Kendaraan</p>
            <p class="font-semibold text-slate-900">{{ $booking->vehicle ? $booking->vehicle->brand.' '.$booking->vehicle->model : '—' }}</p>
            <p class="text-sm text-slate-500">{{ $booking->vehicle->year ?? '' }} · {{ $booking->vehicle->color ?? '' }}</p>
            <p class="text-sm text-slate-500">{{ $booking->vehicle->license_plate ?? '' }}</p>
        </div>
        @if($booking->complaint)
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Keluhan Anda</p>
            <p class="text-sm text-slate-600 leading-relaxed">{{ $booking->complaint }}</p>
        </div>
        @endif
    </div>

    {{-- Services --}}
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100"><h3 class="font-semibold text-sm text-slate-800">Layanan</h3></div>
        <div class="divide-y divide-slate-100">
            @forelse($booking->services as $svc)
            <div class="flex items-center justify-between px-6 py-4">
                <div>
                    <p class="font-medium text-sm text-slate-800">{{ $svc->service_name }}</p>
                    <p class="text-xs text-slate-400">Est. {{ $svc->estimated_time }} menit</p>
                </div>
                <span class="font-semibold text-sm text-slate-900">Rp {{ number_format($svc->pivot->price,0,',','.') }}</span>
            </div>
            @empty
            <div class="text-center py-8 text-slate-400 text-sm">Layanan akan dikonfirmasi oleh tim kami segera.</div>
            @endforelse
        </div>
    </div>

    {{-- Spareparts --}}
    @if($booking->transactionSpareparts->count())
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100"><h3 class="font-semibold text-sm text-slate-800">Sparepart</h3></div>
        <div class="divide-y divide-slate-100">
            @foreach($booking->transactionSpareparts as $sp)
            <div class="flex items-center justify-between px-6 py-4">
                <div>
                    <p class="font-medium text-sm text-slate-800">{{ $sp->sparepart->name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ $sp->qty }} pcs × Rp {{ number_format($sp->price,0,',','.') }}</p>
                </div>
                <span class="font-semibold text-sm text-slate-900">Rp {{ number_format($sp->subtotal,0,',','.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Invoice --}}
    @if($booking->transaction)
    @php $tx=$booking->transaction; $pay=$tx->payment; @endphp
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-sm text-slate-800">Ringkasan Nota</h3>
            <a href="{{ route('customer.bookings.invoice', $booking) }}" target="_blank"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">
                <i class="fas fa-file-pdf text-[10px]"></i> Lihat Nota
            </a>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between text-slate-500"><span>Layanan</span><span>Rp {{ number_format($tx->total_service,0,',','.') }}</span></div>
            <div class="flex justify-between text-slate-500"><span>Sparepart</span><span>Rp {{ number_format($tx->total_sparepart,0,',','.') }}</span></div>
            <div class="flex justify-between font-semibold text-slate-900 border-t border-slate-100 pt-2 mt-1">
                <span>Total Keseluruhan</span>
                <span class="font-display font-bold text-xl">Rp {{ number_format($tx->grand_total,0,',','.') }}</span>
            </div>
        </div>
    </div>

    {{-- Payment --}}
    @if($pay)
    @php $pc=$pay->payment_status==='paid'?'emerald':($pay->payment_status==='partial'?'amber':'red'); @endphp
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-sm text-slate-800">Pembayaran</h3>
            <x-badges.status :status="$pay->payment_status" />
        </div>
        <div class="p-6">
            @if($pay->payment_status === 'paid')
            <div class="flex items-center gap-3 text-emerald-600">
                <i class="fas fa-check-circle text-2xl"></i>
                <div>
                    <p class="font-semibold text-sm">Pembayaran Selesai</p>
                    <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($pay->payment_date)->translatedFormat('d M Y') }} · {{ $pay->payment_method === 'cash' ? 'Tunai' : ucfirst($pay->payment_method ?? '—') }}</p>
                </div>
            </div>
            @else
            <p class="text-sm text-slate-500 mb-5">Selesaikan pembayaran untuk memfinalisasi riwayat servis Anda.</p>
            <a href="{{ route('customer.payments.show', $pay) }}"
               class="w-full flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-3 rounded-xl transition-colors">
                <i class="fas fa-credit-card text-xs"></i> Bayar Sekarang - Rp {{ number_format($tx->grand_total,0,',','.') }}
            </a>
            @endif
        </div>
    </div>
    @endif
    @endif

    {{-- Cancel --}}
    @if(in_array($booking->status, ['pending','confirmed']))
    <form action="{{ route('customer.bookings.cancel', $booking) }}" method="POST"
          data-confirm="Apakah Anda yakin ingin membatalkan booking ini?">
        @csrf
        <button type="submit" class="w-full py-3 rounded-xl border border-red-200 text-red-500 hover:bg-red-50 font-semibold text-sm transition-colors">
            <i class="fas fa-times mr-2"></i> Batalkan Booking
        </button>
    </form>
    @endif
</div>
@endsection
