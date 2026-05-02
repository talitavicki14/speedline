@props([
    'color' => 'slate',
    'class' => ''
])

@php
    $baseClasses = 'inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold capitalize transition-all border';
    
    $colors = [
        'amber'   => 'bg-amber-100 text-amber-700 border-amber-200',
        'blue'    => 'bg-blue-100 text-blue-700 border-blue-200',
        'violet'  => 'bg-violet-100 text-violet-700 border-violet-200',
        'indigo'  => 'bg-indigo-100 text-indigo-700 border-indigo-200',
        'emerald' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'red'     => 'bg-red-100 text-red-700 border-red-200',
        'slate'   => 'bg-slate-100 text-slate-700 border-slate-200',
    ];

    $colorClasses = $colors[$color] ?? $colors['slate'];
@endphp

<span {{ $attributes->merge(['class' => "$baseClasses $colorClasses $class"]) }}>
    {{ $slot }}
</span>
