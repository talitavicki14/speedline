@extends('layouts.admin')
@section('title','Booking #'.$booking->id)
@section('page-title','Detail Booking')

@section('content')
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('admin.bookings.index') }}" class="text-slate-400 hover:text-slate-700 transition-colors">
        <i class="fas fa-arrow-left mr-1"></i> Booking
    </a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-500">Booking #{{ $booking->id }}</span>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    {{-- LEFT COLUMN --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Info --}}
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-sm text-slate-800">Informasi Booking</h3>
                <x-badges.status :status="$booking->status" />
            </div>
            <div class="p-6 grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Pelanggan</p>
                    <p class="font-semibold text-slate-900">{{ $booking->user->name ?? '—' }}</p>
                    <p class="text-sm text-slate-500">{{ $booking->user->email ?? '' }}</p>
                    <p class="text-sm text-slate-500">{{ $booking->user->phone ?? '' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Kendaraan</p>
                    <p class="font-semibold text-slate-900">{{ $booking->vehicle ? $booking->vehicle->brand.' '.$booking->vehicle->model : '—' }}</p>
                    <p class="text-sm text-slate-500">{{ $booking->vehicle->year ?? '' }} · {{ $booking->vehicle->color ?? '' }}</p>
                    <p class="text-sm text-slate-500">{{ $booking->vehicle->license_plate ?? '' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jadwal</p>
                    <p class="font-semibold text-slate-900">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('l, d M Y') }}</p>
                    <p class="text-sm text-slate-500">{{ substr($booking->booking_time,0,5) }} WIB</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Diajukan Pada</p>
                    <p class="font-semibold text-slate-900">{{ $booking->created_at->translatedFormat('d M Y, H:i') }}</p>
                </div>
                @if($booking->complaint)
                <div class="col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Keluhan Pelanggan</p>
                    <p class="text-sm text-slate-600 bg-slate-50 rounded-xl px-4 py-3 border border-slate-100">{{ $booking->complaint }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Services --}}
        @php
            $role            = auth()->user()->role;
            $canEditServices = \Illuminate\Support\Facades\Gate::allows('manage-data')
                               && in_array($role, ['admin','mekanik'])
                               && in_array($booking->status, ['confirmed','in_progress']);
        @endphp
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-sm text-slate-800">Layanan</h3>
                @if($canEditServices)
                <button onclick="openAddService()"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">
                    <i class="fas fa-plus text-[10px]"></i> Tambah Layanan
                </button>
                @endif
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($booking->bookingServices as $bs)
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <p class="font-medium text-sm text-slate-800">{{ $bs->service->service_name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">Est. {{ $bs->service->estimated_time ?? 0 }} menit</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="font-semibold text-sm text-slate-900">Rp {{ number_format($bs->price,0,',','.') }}</span>
                        @if($canEditServices)
                        <form action="{{ route('admin.bookings.remove-service', [$booking, $bs]) }}" method="POST"
                               class="inline" data-confirm="Hapus layanan ini?">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-slate-300 hover:text-red-400 transition-colors">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-400 text-sm">Belum ada layanan yang ditambahkan.</div>
                @endforelse
            </div>
            @if($booking->bookingServices->count())
            <div class="px-6 py-4 border-t border-slate-100 flex justify-between items-center bg-slate-50">
                <span class="text-sm text-slate-500">Total Layanan</span>
                <span class="font-semibold text-lg text-slate-900">
                    Rp {{ number_format($booking->bookingServices->sum('price'),0,',','.') }}
                </span>
            </div>
            @endif
        </div>

        {{-- Spareparts --}}
        @if($booking->status !== 'pending')
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-sm text-slate-800">Sparepart</h3>
                @if($canEditServices)
                <button onclick="openAddSparepart()"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">
                    <i class="fas fa-plus text-[10px]"></i> Tambah Sparepart
                </button>
                @endif
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($booking->transactionSpareparts as $bsp)
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <p class="font-medium text-sm text-slate-800">{{ $bsp->sparepart->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $bsp->sparepart->brand ?? '' }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-xs text-slate-400">×{{ $bsp->qty }}</span>
                        <span class="font-semibold text-sm text-slate-900">Rp {{ number_format($bsp->subtotal,0,',','.') }}</span>
                        @if($canEditServices)
                        <form action="{{ route('admin.bookings.remove-sparepart', [$booking, $bsp]) }}" method="POST"
                               class="inline" data-confirm="Hapus sparepart ini? Stok akan dikembalikan.">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-slate-300 hover:text-red-400 transition-colors">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-400 text-sm">Belum ada sparepart.</div>
                @endforelse
            </div>
            @if($booking->transactionSpareparts->count())
            <div class="px-6 py-4 border-t border-slate-100 flex justify-between items-center bg-slate-50">
                <span class="text-sm text-slate-500">Total Sparepart</span>
                <span class="font-semibold text-lg text-slate-900">
                    Rp {{ number_format($booking->transactionSpareparts->sum('subtotal'),0,',','.') }}
                </span>
            </div>
            @endif
        </div>
        @endif

        {{-- Transaction Summary --}}
        @if($booking->transaction)
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-semibold text-sm text-slate-800">Transaksi</h3>
                <a href="{{ route('admin.transactions.show', $booking->transaction) }}"
                   class="text-xs font-semibold text-slate-500 hover:text-slate-900 hover:underline">
                    Lihat detail →
                </a>
            </div>
            <div class="p-6 grid grid-cols-3 gap-3">
                <div class="bg-slate-50 rounded-xl p-4 text-center border border-slate-100">
                    <p class="text-xs text-slate-400 mb-1">Layanan</p>
                    <p class="font-semibold text-sm">Rp {{ number_format($booking->transaction->total_service,0,',','.') }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 text-center border border-slate-100">
                    <p class="text-xs text-slate-400 mb-1">Sparepart</p>
                    <p class="font-semibold text-sm">Rp {{ number_format($booking->transaction->total_sparepart,0,',','.') }}</p>
                </div>
                <div class="bg-slate-900 text-white rounded-xl p-4 text-center">
                    <p class="text-xs text-slate-400 mb-1">Total Keseluruhan</p>
                    <p class="font-bold text-sm">Rp {{ number_format($booking->transaction->grand_total,0,',','.') }}</p>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- RIGHT PANEL --}}
    <div class="space-y-4">

        {{-- ── STATUS: pending ──────────────────────────────── --}}
        @if($booking->status === 'pending' && \Illuminate\Support\Facades\Gate::allows('manage-data') && in_array($role, ['admin','kasir']))
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-sm text-slate-800">Perbarui Status</h3>
            </div>
            <div class="p-4 flex gap-2">
                <form action="{{ route('admin.bookings.status', $booking) }}" method="POST" class="flex-1"
                      data-confirm="Apakah Anda yakin ingin membatalkan booking ini?">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="cancelled">
                    <button class="w-full py-2 rounded-xl text-sm font-semibold border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                        Batalkan
                    </button>
                </form>
                <form action="{{ route('admin.bookings.status', $booking) }}" method="POST" class="flex-1">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="confirmed">
                    <button class="w-full py-2 rounded-xl text-sm font-semibold border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                        Konfirmasi
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- ── STATUS: confirmed ────────────────────────────── --}}
        @if($booking->status === 'confirmed')
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-sm text-slate-800">Perbarui Status</h3>
            </div>
            <div class="p-4 flex flex-col gap-3">
                <div class="flex gap-2">
                    @if(\Illuminate\Support\Facades\Gate::allows('manage-data') && in_array($role, ['admin','kasir']))
                    <form action="{{ route('admin.bookings.status', $booking) }}" method="POST" class="flex-1"
                          data-confirm="Apakah Anda yakin ingin membatalkan booking ini?">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="cancelled">
                        <button class="w-full py-2 rounded-xl text-sm font-semibold border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                            Batalkan
                        </button>
                    </form>
                    @endif
                    @if(\Illuminate\Support\Facades\Gate::allows('manage-data') && in_array($role, ['admin','mekanik']))
                    <form action="{{ route('admin.bookings.status', $booking) }}" method="POST" class="flex-1">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="in_progress">
                        <button class="w-full py-2 rounded-xl text-sm font-semibold border border-violet-200 bg-violet-50 text-violet-700 hover:bg-violet-100 transition-colors">
                            Mulai Kerja
                        </button>
                    </form>
                    @endif
                </div>

                @if($role === 'kasir')
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
                    <i class="fas fa-tools text-blue-500 text-lg mb-1.5 block"></i>
                    <p class="text-xs font-semibold text-blue-700">Siap untuk Dikerjakan Mekanik</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Booking telah dikonfirmasi. Menunggu mekanik memulai pengerjaan.</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ── STATUS: in_progress ──────────────────────────── --}}
        @if($booking->status === 'in_progress' && \Illuminate\Support\Facades\Gate::allows('manage-data') && in_array($role, ['admin','mekanik']))
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-sm text-slate-800">Perbarui Status</h3>
            </div>
            <div class="p-4">
                @php $hasServices = $booking->bookingServices->count() > 0; @endphp
                <form action="{{ route('admin.bookings.status', $booking) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="ready">
                    <button type="submit" 
                            {{ !$hasServices ? 'disabled' : '' }}
                            class="w-full py-2.5 rounded-xl text-sm font-semibold border {{ $hasServices ? 'border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100' : 'border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed' }} transition-colors">
                        Tandai Siap
                    </button>
                </form>
                @if(!$hasServices)
                <p class="text-[10px] text-red-500 mt-2 text-center font-medium">
                    <i class="fas fa-info-circle mr-1"></i> Tambahkan setidaknya satu layanan untuk menandai siap.
                </p>
                @endif
            </div>
        </div>
        @endif

        {{-- ── STATUS: ready — kasir creates transaction ────── --}}
        @if($booking->status === 'ready')
            @if(!$booking->transaction && \Illuminate\Support\Facades\Gate::allows('manage-data') && in_array($role, ['admin','kasir']))
            <div class="bg-slate-900 text-white rounded-2xl p-5 space-y-3">
                <div>
                    <p class="font-semibold text-sm mb-1">Siap untuk Transaksi</p>
                </div>
                <a href="{{ route('admin.transactions.create', $booking) }}"
                   class="w-full flex items-center justify-center gap-2 bg-white text-slate-900 hover:bg-slate-100 font-semibold text-sm py-2.5 rounded-xl transition-colors">
                    <i class="fas fa-receipt"></i> Buat Transaksi
                </a>
            </div>
            @elseif(!$booking->transaction && $role === 'mekanik')
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5 text-center">
                <i class="fas fa-check-circle text-indigo-400 text-2xl mb-2 block"></i>
                <p class="text-sm font-semibold text-indigo-700">Siap untuk Kasir</p>
                <p class="text-xs text-slate-500 mt-1">Menunggu kasir untuk membuat transaksi.</p>
            </div>
            @elseif($booking->transaction && $booking->transaction->payment?->payment_status === 'unpaid')
            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-5">
                <a href="{{ route('admin.transactions.show', $booking->transaction) }}"
                   class="w-full flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">
                    <i class="fas fa-credit-card"></i> Ke Pembayaran
                </a>
            </div>
            @endif

            @if(\Illuminate\Support\Facades\Gate::allows('manage-data') && in_array($role, ['admin']) && (!$booking->transaction || $booking->transaction->payment?->payment_status !== 'paid'))
            <div>
                <form action="{{ route('admin.bookings.status', $booking) }}" method="POST"
                      data-confirm="Kembalikan ke Dalam Pengerjaan? Mekanik dapat mengedit kembali.">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="in_progress">
                    <button class="w-full py-2 rounded-xl text-xs font-semibold border border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                        <i class="fas fa-undo mr-1"></i> Kembalikan ke Dalam Pengerjaan
                    </button>
                </form>
            </div>
            @endif
        @endif

        {{-- ── Booking Progress Tracker ─────────────────────── --}}
        @if(!in_array($booking->status, ['completed','cancelled']))
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-4">Progres Booking</p>
            @php
            $flowSteps = [
                ['pending',     'Menunggu',      'Pelanggan mengajukan booking'],
                ['confirmed',   'Dikonfirmasi',  'Admin/kasir mengonfirmasi'],
                ['in_progress', 'Dikerjakan',    'Mekanik mengerjakan kendaraan'],
                ['ready',       'Siap',          'Mekanik menandai siap'],
                ['transaction', 'Transaksi',     'Kasir membuat invoice'],
                ['payment',     'Pembayaran',    'Pelanggan membayar'],
                ['completed',   'Selesai',       'Otomatis setelah pembayaran'],
            ];
            $order  = ['pending'=>0,'confirmed'=>1,'in_progress'=>2,'ready'=>3,'transaction'=>4,'payment'=>5,'completed'=>6];
            $curIdx = $order[$booking->status] ?? 0;

            $transactionDone = $booking->transaction !== null;
            $paymentDone     = $transactionDone
                               && $booking->transaction->payment
                               && in_array($booking->transaction->payment->payment_status, ['paid','partial']);
            @endphp

            <div class="space-y-0">
                @foreach($flowSteps as $i => [$key, $label, $desc])
                @php
                    $stepIdx = $order[$key] ?? 0;

                    if ($key === 'transaction') {
                        $isDone = $transactionDone;
                    } elseif ($key === 'payment') {
                        $isDone = $paymentDone;
                    } else {
                        $isDone = $stepIdx <= $curIdx;
                    }

                    $isLast = $i === count($flowSteps) - 1;
                @endphp
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        @if($isDone)
                        <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0 z-10">
                            <i class="fas fa-check text-white" style="font-size:9px"></i>
                        </div>
                        @else
                        <div class="w-6 h-6 rounded-full bg-slate-100 border-2 border-slate-200 flex-shrink-0 z-10"></div>
                        @endif
                        @if(!$isLast)
                        <div class="w-0.5 flex-1 my-1 {{ $isDone ? 'bg-emerald-300' : 'bg-slate-200' }}"></div>
                        @endif
                    </div>
                    <div class="pb-4 min-w-0 pt-0.5">
                        <p class="text-xs font-semibold leading-none
                            {{ $isDone ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ $label }}
                        </p>
                        <p class="text-[10px] text-slate-400 mt-0.5 leading-relaxed">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── Payment Status ───────────────────────────────── --}}
        @if($booking->transaction && $booking->transaction->payment)
        @php
            $pay = $booking->transaction->payment;
            $pc  = $pay->payment_status === 'paid' ? 'emerald' : ($pay->payment_status === 'partial' ? 'amber' : 'red');
            $payLabel = match($pay->payment_status) {
                'paid' => 'Lunas',
                'partial' => 'Cicilan',
                'unpaid' => 'Belum Bayar',
                default => $pay->payment_status
            };
        @endphp
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-sm text-slate-800">Pembayaran</h3>
                <a href="{{ route('admin.transactions.show', $booking->transaction) }}"
                   class="text-xs text-slate-400 hover:text-slate-700 hover:underline">Lihat →</a>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Status</span>
                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold capitalize bg-{{ $pc }}-100 text-{{ $pc }}-700">
                        {{ $payLabel }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Dibayar</span>
                    <span class="font-semibold text-sm">Rp {{ number_format($pay->amount_paid,0,',','.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Total Keseluruhan</span>
                    <span class="font-semibold text-sm">Rp {{ number_format($booking->transaction->grand_total,0,',','.') }}</span>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Add Service Modal --}}
<div id="addServiceModal" class="hidden fixed inset-0 bg-black/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md border border-slate-100">
        <h3 class="font-semibold text-lg text-slate-900 mb-5">Tambah Layanan</h3>
        <form action="{{ route('admin.bookings.add-service', $booking) }}" method="POST">
            @csrf
            <select name="service_id" data-search="true"
                    class="cs-replace w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none mb-4 bg-white text-slate-700">
                <option value="">Pilih layanan...</option>
                @foreach($services as $svc)
                <option value="{{ $svc->id }}">{{ $svc->service_name }}</option>
                @endforeach
            </select>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('addServiceModal')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">
                    Tambah
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Add Sparepart Modal --}}
<div id="addSparepartModal" class="hidden fixed inset-0 bg-black/30 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md border border-slate-100">
        <h3 class="font-semibold text-lg text-slate-900 mb-5">Tambah Sparepart</h3>
        <form action="{{ route('admin.bookings.add-sparepart', $booking) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Sparepart</label>
                <select name="sparepart_id" data-search="true"
                        class="cs-replace w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none bg-white text-slate-700">
                    <option value="">Pilih sparepart...</option>
                    @foreach($spareparts as $sp)
                    <option value="{{ $sp->id }}">[{{ $sp->type }}] {{ $sp->name }} | {{ $sp->brand }} (Stok: {{ $sp->stock }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Jumlah</label>
                <input type="number" name="qty" min="1" value="1" required
                       class="w-full border border-slate-200 focus:border-slate-900 rounded-xl px-4 py-3 text-sm outline-none transition-colors text-slate-700">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('addSparepartModal')"
                        class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm py-2.5 rounded-xl transition-colors">
                    Tambah
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/js/pages/admin/bookings/show.js')
@endpush
