@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center gap-1.5">
        @if ($paginator->onFirstPage())
            <span class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-100 text-slate-300 cursor-not-allowed">
                <i class="fas fa-chevron-left text-[10px]"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                <i class="fas fa-chevron-left text-[10px]"></i>
            </a>
        @endif

        @php
            $total = $paginator->lastPage();
            $current = $paginator->currentPage();

            if ($total <= 7) {
                $pages = range(1, $total);
            } elseif ($current <= 4) {
                $pages = [1, 2, 3, 4, 5, '...', $total];
            } elseif ($current >= $total - 3) {
                $pages = [1, '...', $total - 4, $total - 3, $total - 2, $total - 1, $total];
            } else {
                $pages = [1, '...', $current - 1, $current, $current + 1, '...', $total];
            }
        @endphp

        @foreach ($pages as $page)
            @if ($page === '...')
                <span class="w-8 h-8 hidden sm:flex items-center justify-center text-slate-400 text-xs">...</span>
            @elseif ($page == $current)
                <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-900 text-white text-xs font-bold shadow-sm">
                    {{ $page }}
                </span>
            @else
                <a href="{{ $paginator->url($page) }}" class="w-8 h-8 hidden sm:flex items-center justify-center rounded-lg border border-slate-200 text-slate-600 text-xs hover:bg-slate-50 hover:border-slate-300 transition-colors">
                    {{ $page }}
                </a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-colors">
                <i class="fas fa-chevron-right text-[10px]"></i>
            </a>
        @endif
    </nav>
@endif
