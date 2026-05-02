<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota #TRX-{{ str_pad($booking->transaction->id, 4, '0', STR_PAD_LEFT) }}</title>
    <style>
        {!! file_get_contents(resource_path('css/pdf/invoice.css')) !!}
    </style>
</head>
<body>
    @php
        $transaction = $booking->transaction;
        $payment = $transaction->payment;
        $amountPaid = $payment->amount_paid ?? 0;
        $balanceDue = $transaction->grand_total - $amountPaid;
    @endphp

    @if($payment->payment_status === 'paid')
        <div class="paid-stamp">LUNAS</div>
    @elseif($payment->payment_status === 'partial')
        <div class="partial-stamp">DICICIL</div>
    @endif

    <div class="header">
        <table>
            <tr>
                <td>
                    <img src="{{ public_path('images/logo_black.png') }}" class="logo">
                </td>
                <td class="company-info">
                    <div class="company-name">SPEEDLINE AUTOMOTIVE</div>
                    <div>Jl. Otomotif No. 123, Jakarta Selatan</div>
                    <div>Telp: (021) 000-0000 | Email: hello@speedline.com</div>
                </td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td>
                <div class="invoice-title">Nota</div>
                <div style="color: #64748b;">No: #TRX-{{ str_pad($transaction->id, 4, '0', STR_PAD_LEFT) }}</div>
            </td>
            <td class="text-right">
                <div class="section-label">Tanggal Terbit</div>
                <div class="info-value">{{ $transaction->created_at->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="details-grid">
        <tr>
            <td>
                <div class="section-label">Ditujukan Untuk</div>
                <div class="info-value" style="font-size: 16px;">{{ $booking->user->name ?? '—' }}</div>
                <div style="color: #64748b;">{{ $booking->user->phone ?? '' }}</div>
                <div style="color: #64748b;">{{ $booking->user->email ?? '' }}</div>
            </td>
            <td>
                <div class="section-label">Detail Kendaraan</div>
                <div class="info-value">{{ $booking->vehicle->brand ?? '' }} {{ $booking->vehicle->model ?? '' }}</div>
                <div style="color: #64748b;">No. Plat: {{ $booking->vehicle->license_plate ?? '—' }}</div>
                <div style="color: #64748b;">Tahun: {{ $booking->vehicle->year ?? '—' }} | Warna: {{ $booking->vehicle->color ?? '—' }}</div>
            </td>
        </tr>
    </table>

    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 60%;">Deskripsi</th>
                <th class="text-center" style="width: 15%;">Jumlah</th>
                <th class="text-right" style="width: 25%;">Harga</th>
            </tr>
        </thead>
        <tbody>
            {{-- Services --}}
            @foreach($booking->services as $svc)
            <tr>
                <td>
                    <div class="info-value">{{ $svc->service_name }}</div>
                    <div style="font-size: 11px; color: #64748b;">Jasa Bengkel</div>
                </td>
                <td class="text-center">1</td>
                <td class="text-right">Rp {{ number_format($svc->pivot->price, 0, ',', '.') }}</td>
            </tr>
            @endforeach

            {{-- Spareparts --}}
            @foreach($booking->transactionSpareparts as $sp)
            <tr>
                <td>
                    <div class="info-value">{{ $sp->sparepart->name ?? '—' }}</div>
                    <div style="font-size: 11px; color: #64748b;">{{ $sp->sparepart->brand ?? '' }}</div>
                </td>
                <td class="text-center">{{ $sp->qty }}</td>
                <td class="text-right">Rp {{ number_format($sp->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td style="color: #64748b;">Subtotal Jasa</td>
                <td class="text-right">Rp {{ number_format($transaction->total_service, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="color: #64748b;">Subtotal Sparepart</td>
                <td class="text-right">Rp {{ number_format($transaction->total_sparepart, 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total">
                <td style="font-weight: bold;">Total Keseluruhan</td>
                <td class="text-right" style="font-weight: bold;">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="color: #059669; font-weight: bold;">{{ $payment->payment_status === 'paid' ? 'Jumlah Dibayar' : 'Pembayaran Diterima' }}</td>
                <td class="text-right" style="color: #059669; font-weight: bold;">Rp {{ number_format($amountPaid, 0, ',', '.') }}</td>
            </tr>
            @if($balanceDue > 0)
            <tr>
                <td style="color: #d97706; font-weight: bold;">Sisa Tagihan</td>
                <td class="text-right" style="color: #d97706; font-weight: bold;">Rp {{ number_format($balanceDue, 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div style="margin-top: 40px; padding: 15px; background-color: #f8fafc; border-radius: 8px;">
        <div class="section-label">Informasi Pembayaran</div>
        <div style="margin-bottom: 5px;">Status: 
            <span class="status-badge {{ $payment->payment_status === 'paid' ? 'status-paid' : 'status-partial' }}">
                {{ $payment->payment_status === 'paid' ? 'Lunas' : 'Dibayar Sebagian' }}
            </span>
        </div>
        <div style="color: #64748b;">Metode: {{ $payment->payment_method === 'cash' ? 'Tunai' : ucfirst($payment->payment_method ?? '—') }}</div>
        @if($payment->payment_status === 'paid')
        <div style="color: #64748b;">Tanggal Bayar: {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d/m/Y') : '—' }}</div>
        @endif
    </div>

    <div class="footer">
        <p>Terima kasih telah memilih Speedline Automotive untuk kebutuhan kendaraan Anda.</p>
        <p>Dokumen ini dibuat secara otomatis oleh sistem. Tidak memerlukan tanda tangan.</p>
        <p>&copy; {{ date('Y') }} Speedline Automotive. Seluruh hak cipta dilindungi undang-undang.</p>
    </div>

</body>
</html>
