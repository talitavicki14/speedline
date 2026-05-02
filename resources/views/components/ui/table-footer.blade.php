@props([
    'paginator',
    'except' => ['page', 'per_page'],
    'append' => []
])

@php
    $per_page_options = [
        ['value' => 10, 'label' => 10],
        ['value' => 25, 'label' => 25],
        ['value' => 50, 'label' => 50]
    ];
@endphp

<div class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 sm:py-3 border-t border-slate-100 bg-slate-50 gap-4 sm:gap-2">
    <div class="flex items-center gap-3 order-2 sm:order-1">
        <span class="text-xs text-slate-400">Rows:</span>
        <form method="GET" data-auto-filter>
            @foreach(request()->except($except) as $k => $v)
                @if(is_array($v))
                    @foreach($v as $val)
                        <input type="hidden" name="{{ $k }}[]" value="{{ $val }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endif
            @endforeach

            @foreach($append as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach

            <div data-custom-select
                 data-name="per_page"
                 data-value="{{ $paginator->perPage() }}"
                 data-placeholder="{{ $paginator->perPage() }}"
                 data-size="sm"
                 data-pagination-footer="true"
                 data-hide-placeholder="true"
                 data-options='@json($per_page_options)'
                 class="w-18"></div>
        </form>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-3 order-1 sm:order-2">
        @if($paginator->total() > 0)
        <span class="text-xs text-slate-400">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </span>
        @endif

        @if($paginator->hasPages())
        <div class="flex items-center">
            {{ $paginator->onEachSide(1)->links('components.ui.pagination') }}
        </div>
        @endif
    </div>
</div>
