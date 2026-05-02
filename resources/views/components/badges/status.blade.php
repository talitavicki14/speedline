@props(['status'])

@php
    $mapping = [
        'pending'     => 'amber',
        'confirmed'   => 'blue',
        'in_progress' => 'violet',
        'ready'       => 'indigo',
        'completed'   => 'emerald',
        'cancelled'   => 'red',
        'paid'        => 'emerald',
        'partial'     => 'amber',
        'unpaid'      => 'red',
        'expired'     => 'slate',
        'failed'      => 'red',
    ];

    $labels = [
        'pending'     => 'Menunggu',
        'confirmed'   => 'Dikonfirmasi',
        'in_progress' => 'Dikerjakan',
        'ready'       => 'Siap Diambil',
        'completed'   => 'Selesai',
        'cancelled'   => 'Dibatalkan',
        'paid'        => 'Lunas',
        'partial'     => 'Cicilan',
        'unpaid'      => 'Belum Bayar',
        'expired'     => 'Kedaluwarsa',
        'failed'      => 'Gagal',
    ];

    $color = (is_string($status) && isset($mapping[$status])) ? $mapping[$status] : 'slate';
    $label = (is_string($status) && isset($labels[$status])) ? $labels[$status] : str_replace('_', ' ', $status);
@endphp

<x-ui.badge :color="$color" {{ $attributes }}>
    {{ $label }}
</x-ui.badge>
