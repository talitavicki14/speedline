<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-date" content="{{ now()->toDateString() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <title>@yield('title', 'Dashboard') | Speedline Automotive</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen"
      data-flash-success="{{ session('success') }}"
      data-flash-error="{{ $errors->any() ? $errors->first() : session('error') }}"
      data-flash-warning="{{ session('warning') }}"
      data-flash-info="{{ session('info') }}">
<div class="page-progress"></div>

<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/40 z-40 lg:hidden hidden transition-opacity duration-300 opacity-0"></div>

{{-- Sidebar --}}
<aside id="sidebar" class="w-60 bg-white border-r border-slate-100 flex flex-col fixed inset-y-0 left-0 z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="px-5 h-16 flex items-center justify-between border-b border-slate-100">
        <a href="{{ route('landing') }}" class="block hover:opacity-80 transition-opacity">
            <img src="{{ asset('images/logo_black.png') }}" alt="Speedline Automotive" class="h-8 w-auto object-contain">
        </a>
        <button id="sidebarClose" class="lg:hidden text-slate-400 hover:text-slate-600 transition-colors">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <p class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase px-3 pb-1.5 pt-1">Menu Utama</p>
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.dashboard') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
            <i class="fas fa-chart-pie w-4 text-center {{ request()->routeIs('admin.dashboard') ? 'text-slate-800' : 'text-slate-400' }}"></i> Dashboard
        </a>

        @php $userRole = auth()->user()->role; @endphp
        
        @if(in_array($userRole, ['admin', 'owner', 'kasir']))
        <p class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase px-3 pb-1.5 pt-4">Operasional</p>
        
        @if(in_array($userRole, ['admin', 'owner', 'kasir']))
        <a href="{{ route('admin.bookings.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.bookings*') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
            <i class="fas fa-calendar-check w-4 text-center {{ request()->routeIs('admin.bookings*') ? 'text-slate-800' : 'text-slate-400' }}"></i> Booking
        </a>
        @endif

        <a href="{{ route('admin.transactions.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.transactions*') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
            <i class="fas fa-receipt w-4 text-center {{ request()->routeIs('admin.transactions*') ? 'text-slate-800' : 'text-slate-400' }}"></i> Transaksi
        </a>

        @if($userRole === 'kasir')
        <a href="{{ route('admin.cashier.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.cashier*') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
            <i class="fas fa-cash-register w-4 text-center {{ request()->routeIs('admin.cashier*') ? 'text-slate-800' : 'text-slate-400' }}"></i> Kasir Retail
        </a>
        @endif

        <a href="{{ route('admin.payments.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.payments*') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
            <i class="fas fa-credit-card w-4 text-center {{ request()->routeIs('admin.payments*') ? 'text-slate-800' : 'text-slate-400' }}"></i> Pembayaran
        </a>
        @endif

        {{-- Laporan --}}
        @if($userRole === 'owner')
        <p class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase px-3 pb-1.5 pt-4">Analisa & Laporan</p>
        <div class="sidebar-dropdown">
            <button class="sidebar-dropdown-toggle flex items-center justify-between w-full gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.reports*') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                <div class="flex items-center gap-2.5">
                    <i class="fas fa-file-invoice-dollar w-4 text-center {{ request()->routeIs('admin.reports*') ? 'text-slate-800' : 'text-slate-400' }}"></i> Laporan
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-300 {{ request()->routeIs('admin.reports*') ? 'rotate-180' : '' }}"></i>
            </button>
            <div class="sidebar-dropdown-content space-y-0.5 mt-1 pl-4 {{ request()->routeIs('admin.reports*') ? '' : 'hidden' }}">
                <a href="{{ route('admin.reports.finance') }}"
                   class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.reports.finance') ? 'text-slate-900 font-bold bg-slate-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                    <i class="fas fa-chart-line w-4 text-center {{ request()->routeIs('admin.reports.finance') ? 'text-slate-800' : 'text-slate-400' }}"></i> Laporan Keuangan
                </a>
                <a href="{{ route('admin.reports.sales') }}"
                   class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.reports.sales') ? 'text-slate-900 font-bold bg-slate-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                    <i class="fas fa-shopping-cart w-4 text-center {{ request()->routeIs('admin.reports.sales') ? 'text-slate-800' : 'text-slate-400' }}"></i> Laporan Penjualan
                </a>
                <a href="{{ route('admin.reports.purchases') }}"
                   class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.reports.purchases') ? 'text-slate-900 font-bold bg-slate-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                    <i class="fas fa-truck-loading w-4 text-center {{ request()->routeIs('admin.reports.purchases') ? 'text-slate-900 font-bold bg-slate-50' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}"></i> Laporan Pembelian
                </a>
            </div>
        </div>
        @endif

        {{-- Mekanik --}}
        @if($userRole === 'mekanik')
        <p class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase px-3 pb-1.5 pt-4">Pekerjaan Saya</p>
        <a href="{{ route('admin.bookings.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.bookings*') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
            <i class="fas fa-wrench w-4 text-center {{ request()->routeIs('admin.bookings*') ? 'text-slate-800' : 'text-slate-400' }}"></i> Perintah Kerja
        </a>
        @endif

        {{-- Master Data & Management --}}
        @if(in_array($userRole, ['admin', 'owner']))
        <p class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase px-3 pb-1.5 pt-4">Katalog</p>
        <a href="{{ route('admin.services.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.services*') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
            <i class="fas fa-cogs w-4 text-center {{ request()->routeIs('admin.services*') ? 'text-slate-800' : 'text-slate-400' }}"></i> Layanan
        </a>
        <a href="{{ route('admin.spareparts.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.spareparts*') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
            <i class="fas fa-box w-4 text-center {{ request()->routeIs('admin.spareparts*') ? 'text-slate-800' : 'text-slate-400' }}"></i> Sparepart
        </a>
        <a href="{{ route('admin.distributors.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.distributors*') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
            <i class="fas fa-truck w-4 text-center {{ request()->routeIs('admin.distributors*') ? 'text-slate-800' : 'text-slate-400' }}"></i> Distributor
        </a>

        <p class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase px-3 pb-1.5 pt-4">Manajemen</p>
        @if(in_array($userRole, ['admin', 'owner']))
        <a href="{{ route('admin.heroes.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.heroes*') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
            <i class="fas fa-images w-4 text-center {{ request()->routeIs('admin.heroes*') ? 'text-slate-800' : 'text-slate-400' }}"></i> Banner
        </a>
        @endif
        <a href="{{ route('admin.users.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('admin.users*') ? 'nav-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
            <i class="fas fa-users w-4 text-center {{ request()->routeIs('admin.users*') ? 'text-slate-800' : 'text-slate-400' }}"></i> Pengguna
        </a>
        @endif
    </nav>

    {{-- Account section --}}
    <div class="p-3 border-t border-slate-100 relative">
        <button id="accountToggle"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors text-left">
            <div class="w-8 h-8 rounded-full bg-slate-900 overflow-hidden flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                @if(auth()->user()->photo)
                    <img src="{{ asset('storage/' . auth()->user()->photo) }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800 truncate">{{ auth()->user()->name }}</p>
                @php
                    $roleLabel = match(auth()->user()->role) {
                        'owner' => 'Pemilik',
                        'admin' => 'Administrator',
                        'kasir' => 'Kasir',
                        'mekanik' => 'Mekanik',
                        default => auth()->user()->role
                    };
                @endphp
                <p class="text-xs text-slate-400 capitalize">{{ $roleLabel }}</p>
            </div>
            <i id="accountChevron" class="fas fa-chevron-down text-xs text-slate-400 transition-transform"></i>
        </button>
        
        <div id="accountPanel"
             class="hidden absolute bottom-full left-3 right-3 mb-2 bg-white border border-slate-200 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 overflow-hidden">
                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage/' . auth()->user()->photo) }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-sm text-slate-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                        <span class="inline-flex mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 capitalize">{{ $roleLabel }}</span>
                    </div>
                </div>
            </div>
            <div class="p-2 space-y-1">
                <a href="{{ route('admin.profile') }}" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm text-slate-700 hover:bg-slate-50 transition-colors font-medium">
                    <i class="fas fa-user-circle w-4 text-center text-slate-400"></i> Profil Saya
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm text-red-600 hover:bg-red-50 transition-colors font-medium">
                        <i class="fas fa-sign-out-alt w-4 text-center"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

<div class="lg:ml-60 transition-all duration-300">
    <header class="bg-white border-b border-slate-100 px-7 min-h-[4rem] py-3 flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center">
            <button id="sidebarToggle" class="lg:hidden mr-4 text-slate-500 hover:text-slate-800 transition-colors">
                <i class="fas fa-bars text-lg"></i>
            </button>
            <h1 class="font-display font-bold text-lg text-slate-900">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="flex items-center gap-5">
            @php
                $user = auth()->user();
                $pendingCount = 0;
                $lowStockCount = 0;
                $outStockCount = 0;
                $confirmedCount = 0;
                $revenueTrend = null;
                
                if (in_array($user->role, ['admin', 'owner', 'kasir'])) {
                    $pendingCount = \App\Models\Booking::where('status','pending')->count();
                }

                if (in_array($user->role, ['admin', 'owner'])) {
                    $lowStockCount = \App\Models\Sparepart::whereBetween('stock', [1, 15])->count();
                    $outStockCount = \App\Models\Sparepart::where('stock', '<=', 0)->count();
                }

                if (in_array($user->role, ['admin', 'owner', 'mekanik'])) {
                    $confirmedCount = \App\Models\Booking::where('status','confirmed')->count();
                }

                if ($user->role === 'owner') {
                    $todayRev = \App\Models\Payment::where('payment_status', 'paid')->whereDate('payment_date', today())->sum('amount_paid');
                    $yestRev  = \App\Models\Payment::where('payment_status', 'paid')->whereDate('payment_date', today()->subDay())->sum('amount_paid');
                    
                    if ($todayRev > 0 || $yestRev > 0) {
                        $diff = $todayRev - $yestRev;
                        if ($diff != 0) {
                            $revenueTrend = [
                                'type' => $diff > 0 ? 'up' : 'down',
                                'amount' => abs($diff),
                                'label' => $diff > 0 ? 'Pendapatan naik!' : 'Pendapatan turun'
                            ];
                        }
                    }
                }

                $totalNotif = $pendingCount + $lowStockCount + $outStockCount + $confirmedCount + ($revenueTrend ? 1 : 0);
            @endphp
            <div class="relative">
                <button id="notifBtn" class="relative text-slate-400 hover:text-slate-700 transition-colors p-1">
                    <i class="fas fa-bell text-sm"></i>
                    @if($totalNotif > 0)
                    <span class="absolute -top-1.5 -right-2 bg-slate-900 text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center">{{ $totalNotif }}</span>
                    @endif
                </button>

                <div id="notifPanel"
                     class="hidden absolute right-0 top-full mt-2 w-72 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <h4 class="font-semibold text-sm text-slate-800">Notifikasi</h4>
                        @if($totalNotif > 0)
                        <span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">total {{ $totalNotif }}</span>
                        @endif
                    </div>
                    <div class="p-3">
                        @if($revenueTrend)
                        <div class="flex items-start gap-3 p-3 rounded-xl {{ $revenueTrend['type'] === 'up' ? 'bg-emerald-50 border-emerald-100' : 'bg-rose-50 border-rose-100' }} border mb-2">
                            <div class="w-8 h-8 rounded-full {{ $revenueTrend['type'] === 'up' ? 'bg-emerald-100' : 'bg-rose-100' }} flex items-center justify-center flex-shrink-0">
                                <i class="fas {{ $revenueTrend['type'] === 'up' ? 'fa-arrow-trend-up text-emerald-600' : 'fa-arrow-trend-down text-rose-600' }} text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">{{ $revenueTrend['label'] }}</p>
                                <p class="text-[10px] {{ $revenueTrend['type'] === 'up' ? 'text-emerald-700' : 'text-rose-700' }} mt-0.5">
                                    {{ $revenueTrend['type'] === 'up' ? '+' : '-' }}Rp {{ number_format($revenueTrend['amount'], 0, ',', '.') }} vs kemarin
                                </p>
                            </div>
                        </div>
                        @endif

                        @if($confirmedCount > 0)
                        <a href="{{ route('admin.bookings.index', ['status' => 'confirmed']) }}" 
                           class="flex items-start gap-3 p-3 rounded-xl bg-blue-50 border border-blue-100 mb-2 hover:bg-blue-100/50 transition-colors group">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-200 transition-colors">
                                <i class="fas fa-wrench text-blue-600 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">{{ $confirmedCount }} Pekerjaan Siap</p>
                                <span class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold text-blue-700">
                                    @if(in_array($user->role, ['admin', 'owner']))
                                        Pantau Progres <i class="fas fa-arrow-right text-[10px]"></i>
                                    @else
                                        Mulai Kerja <i class="fas fa-arrow-right text-[10px]"></i>
                                    @endif
                                </span>
                            </div>
                        </a>
                        @endif

                        @if($pendingCount > 0)
                        <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" 
                           class="flex items-start gap-3 p-3 rounded-xl bg-amber-50 border border-amber-100 mb-2 hover:bg-amber-100/50 transition-colors group">
                            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-200 transition-colors">
                                <i class="fas fa-hourglass-half text-amber-600 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">{{ $pendingCount }} Booking Menunggu</p>
                                <span class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold text-amber-700">
                                    Lihat Booking <i class="fas fa-arrow-right text-[10px]"></i>
                                </span>
                            </div>
                        </a>
                        @endif

                        @if($outStockCount > 0)
                        <a href="{{ route('admin.spareparts.index', ['stock_status' => 'out']) }}"
                           class="flex items-start gap-3 p-3 rounded-xl bg-rose-50 border border-rose-100 mb-2 hover:bg-rose-100/50 transition-colors group">
                            <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-200 transition-colors">
                                <i class="fas fa-ban text-rose-600 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">{{ $outStockCount }} Stok Habis</p>
                                <span class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold text-rose-700">
                                    Segera Order <i class="fas fa-arrow-right text-[10px]"></i>
                                </span>
                            </div>
                        </a>
                        @endif

                        @if($lowStockCount > 0)
                        <a href="{{ route('admin.spareparts.index', ['stock_status' => 'low']) }}"
                           class="flex items-start gap-3 p-3 rounded-xl bg-amber-50 border border-amber-100 mb-2 hover:bg-amber-100/50 transition-colors group">
                            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-200 transition-colors">
                                <i class="fas fa-exclamation-triangle text-amber-600 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">{{ $lowStockCount }} Stok Menipis</p>
                                <span class="inline-flex items-center gap-1.5 mt-2 text-xs font-semibold text-amber-700">
                                    Tambah Stok <i class="fas fa-arrow-right text-[10px]"></i>
                                </span>
                            </div>
                        </a>
                        @endif

                        @if($totalNotif == 0)
                        <div class="text-center py-6">
                            <i class="fas fa-check-circle text-emerald-400 text-2xl mb-2 block"></i>
                            <p class="text-sm text-slate-500">Tidak ada notifikasi</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <span class="text-sm text-slate-400">{{ now()->translatedFormat('d M Y') }}</span>
        </div>
    </header>
    <main class="p-7">
        @yield('content')
    </main>
</div>
<x-modals.image-preview />
@stack('scripts')
</body>
</html>
