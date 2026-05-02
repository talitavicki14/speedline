<div id="receiptContent" style="display:none">
    <div style="text-align:center;margin-bottom:12px">
        <img src="{{ asset('images/logo_black.png') }}" style="width: 120px; height: auto; margin-bottom: 8px;">
        <br>
        <strong style="font-size:14px">SPEEDLINE AUTOMOTIVE</strong><br>
        <span style="font-size:10px">Bengkel Resmi Kendaraan</span><br>
        <span style="font-size:9px">Telp: (021) 000-0000</span>
    </div>
    <div style="border-top:1px dashed #000;margin:6px 0"></div>
    <div style="font-size:10px;margin-bottom:4px">
        <div>Nota: <strong>#TRX-{{ str_pad($transaction->id,4,'0',STR_PAD_LEFT) }}</strong></div>
        <div>Tgl: {{ $transaction->created_at->format('d/m/Y H:i') }}</div>
        @if($transaction->booking)
            <div>Nama: {{ $transaction->booking->user->name ?? '-' }}</div>
            <div>Kendaraan: {{ ($transaction->booking->vehicle->brand ?? '').' '.($transaction->booking->vehicle->model ?? '') }}</div>
            <div>Nomor Plat: {{ $transaction->booking->vehicle->license_plate ?? '—' }}</div>
            <div>Mekanik: {{ $transaction->mekanik->name ?? '—' }}</div>
        @endif
        <div>Kasir: {{ $transaction->kasir->name ?? '—' }}</div>
    </div>
    <div style="border-top:1px dashed #000;margin:6px 0"></div>

    @php $bookingServices = $transaction->booking->bookingServices ?? collect(); @endphp
    @if($bookingServices->count() > 0)
    <div style="font-size:10px">
        <strong>Jasa Servis:</strong>  
        @foreach($bookingServices as $bs)
        <div style="display:flex;justify-content:space-between;margin-top:2px">
            <span>{{ $bs->service->service_name ?? '—' }}</span>
            <span>Rp {{ number_format($bs->price,0,',','.') }}</span>
        </div>
        @endforeach
    </div>
    <div style="border-top:1px dashed #000;margin:6px 0;opacity:0.3"></div>
    @endif

    @php $transactionSpareparts = $transaction->transactionSpareparts; @endphp
    @if($transactionSpareparts->count() > 0)
    <div style="font-size:10px">
        <strong>Sparepart:</strong>
        @foreach($transactionSpareparts as $bsp)
        <div style="display:flex;justify-content:space-between;margin-top:2px">
            <span>{{ $bsp->qty }}x {{ $bsp->sparepart->name ?? '—' }}</span>
            <span>Rp {{ number_format($bsp->subtotal,0,',','.') }}</span>
        </div>
        @endforeach
    </div>
    @endif

    <div style="border-top:1px dashed #000;margin:6px 0"></div>
    <div style="font-size:10px">
        @if($transaction->total_service > 0)
        <div style="display:flex;justify-content:space-between">
            <span>Total Jasa</span><span>Rp {{ number_format($transaction->total_service,0,',','.') }}</span>
        </div>
        @endif
        @if($transaction->total_sparepart > 0)
        <div style="display:flex;justify-content:space-between">
            <span>Total Sparepart</span><span>Rp {{ number_format($transaction->total_sparepart,0,',','.') }}</span>
        </div>
        @endif
    </div>
    <div style="border-top:1px dashed #000;margin:6px 0"></div>
    <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:bold">
        <span>TOTAL</span><span>Rp {{ number_format($transaction->grand_total,0,',','.') }}</span>
    </div>
    @if($transaction->payment)
    <div style="font-size:10px;margin-top:4px">
        <div style="display:flex;justify-content:space-between">
            <span>Jumlah Dibayar</span><span>Rp {{ number_format($transaction->payment->amount_paid,0,',','.') }}</span>
        </div>
        @php 
            $change = $transaction->payment->amount_paid - $transaction->grand_total;
        @endphp
        @if($transaction->payment->payment_method === 'cash' && $change > 0)
        <div style="display:flex;justify-content:space-between">
            <span>Kembalian</span><span>Rp {{ number_format($change, 0, ',', '.') }}</span>
        </div>
        @endif
        <div style="display:flex;justify-content:space-between">
            <span>Metode</span><span>{{ $transaction->payment->payment_method === 'cash' ? 'Tunai' : ($transaction->payment->payment_method ?? '—') }}</span>
        </div>
    </div>
    @endif
    <div style="border-top:1px dashed #000;margin:8px 0"></div>
    <div style="text-align:center;font-size:9px">
        Terima kasih atas kepercayaan Anda.<br>
        Semoga kendaraan Anda selalu prima!
    </div>
</div>
