@extends('layouts.customer')
@section('title','Booking Saya')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="font-display font-bold text-2xl text-slate-900">Booking Saya</h1>
        <p class="text-slate-400 text-sm mt-0.5">Semua jadwal servis Anda</p>
    </div>
    <a href="{{ route('customer.bookings.create') }}" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus text-xs"></i> Booking Baru
    </a>
</div>

<div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="divide-y divide-slate-100">
        @forelse($bookings as $b)
        <div class="flex items-center gap-4 px-6 py-5 hover:bg-slate-50 transition-colors">
            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-car text-slate-400"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2.5 mb-1">
                    <p class="font-semibold text-sm text-slate-800">{{ $b->vehicle ? $b->vehicle->brand.' '.$b->vehicle->model : '—' }}</p>
                    <x-badges.status :status="$b->status" class="text-[10px] px-2 py-0.5" />
                </div>
                <p class="text-xs text-slate-400">
                    <i class="fas fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($b->booking_date)->translatedFormat('d M Y') }}
                    &nbsp;·&nbsp;<i class="fas fa-clock mr-1"></i>{{ substr($b->booking_time,0,5) }}
                    &nbsp;·&nbsp;{{ $b->services->count() }} layanan
                </p>
            </div>
            @if($b->transaction && $b->transaction->payment)
            @php 
                $payStatus = $b->transaction->payment->payment_status;
                $pc = $payStatus === 'paid' ? 'emerald' : ($payStatus === 'partial' ? 'amber' : 'red'); 
                $payLabel = match($payStatus) {
                    'paid' => 'Lunas',
                    'partial' => 'Cicilan',
                    'unpaid' => 'Belum Bayar',
                    default => $payStatus
                };
            @endphp
            <div class="text-right hidden sm:block">
                <p class="font-semibold text-sm text-slate-900">Rp {{ number_format($b->transaction->grand_total,0,',','.') }}</p>
                <span class="text-[10px] font-semibold capitalize text-{{ $pc }}-600">{{ $payLabel }}</span>
            </div>
            @endif
            <a href="{{ route('customer.bookings.show', $b) }}" class="w-9 h-9 rounded-xl border border-slate-200 hover:border-slate-400 flex items-center justify-center text-slate-400 hover:text-slate-700 transition-colors flex-shrink-0">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
        </div>
        @empty
        <div class="text-center py-20 text-slate-400">
            <i class="fas fa-calendar-times text-5xl block mb-4 text-slate-200"></i>
            <p class="text-sm mb-5">Belum ada booking.</p>
            <a href="{{ route('customer.bookings.create') }}" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">Buat Booking Pertama Anda</a>
        </div>
        @endforelse
    </div>
    @if($bookings->hasPages())<div class="px-6 py-4 border-t border-slate-100">{{ $bookings->links() }}</div>@endif
</div>
@endsection
