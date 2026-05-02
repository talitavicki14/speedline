<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        {!! file_get_contents(resource_path('css/pdf/export.css')) !!}
    </style>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border: none;">
        <tr>
            <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo_black.png'))) }}"
                    style="width: 150px;">
            </td>
            <td style="width: 50%; text-align: right; vertical-align: top; border: none; padding: 0;">
                <h1 style="margin: 0;">Speedline Automotive - Laporan Pembayaran</h1>
                <div class="meta" style="margin: 4px 0 0;">Dibuat: {{ now()->translatedFormat('d F Y, H:i') }} &nbsp;|&nbsp;
                    Total data: {{ $payments->count() }}</div>
            </td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>Nota</th>
                <th>Pelanggan</th>
                <th>Total Keseluruhan</th>
                <th>Jumlah Dibayar</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $p)
                @php
                    $payLabel = match($p->payment_status) {
                        'paid' => 'Lunas',
                        'partial' => 'Cicilan',
                        'unpaid' => 'Belum Bayar',
                        default => $p->payment_status
                    };
                    $methodLabel = match($p->payment_method) {
                        'cash' => 'Tunai',
                        'midtrans' => 'Midtrans',
                        default => $p->payment_method
                    };
                @endphp
                <tr>
                    <td style="font-family:monospace">#TRX-{{ str_pad($p->transaction_id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $p->transaction->booking->user->name ?? '—' }}</td>
                    <td>Rp {{ number_format($p->transaction->grand_total ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($p->amount_paid, 0, ',', '.') }}</td>
                    <td>{{ $methodLabel ?? '—' }}</td>
                    <td><span class="badge {{ $p->payment_status }}">{{ $payLabel }}</span></td>
                    <td>{{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->translatedFormat('d-m-Y') : $p->created_at->translatedFormat('d-m-Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:20px;color:#94a3b8">Data tidak ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">Speedline Automotive &nbsp;·&nbsp; Rahasia</div>
</body>

</html>
