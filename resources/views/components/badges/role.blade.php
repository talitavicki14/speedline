@props(['role'])

@php
    $mapping = [
        'admin'    => 'slate',
        'owner'    => 'violet',
        'mekanik'  => 'blue',
        'kasir'    => 'amber',
        'customer' => 'emerald',
    ];
    $color = $mapping[$role] ?? 'slate';
@endphp

<x-ui.badge :color="$color" {{ $attributes }}>
    {{ $role }}
</x-ui.badge>
