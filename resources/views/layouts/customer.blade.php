<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <title>@yield('title', 'Akun Saya') | Speedline Automotive</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 min-h-full flex flex-col"
      data-flash-success="{{ session('success') }}"
      data-flash-error="{{ $errors->any() ? $errors->first() : session('error') }}"
      data-flash-warning="{{ session('warning') }}"
      data-flash-info="{{ session('info') }}"
      data-flash-verified="{{ session('verified') ? 'true' : 'false' }}">
<div class="page-progress"></div>

<header class="bg-white border-b border-slate-100 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
        <a href="{{ route('landing') }}">
            <img src="{{ asset('images/logo_black.png') }}" alt="Speedline Automotive" class="h-8 w-auto object-contain">
        </a>
        <nav class="hidden md:flex items-center">
            <a href="{{ route('customer.dashboard') }}"
               class="nav-link px-4 py-5 text-sm border-b-2 border-transparent transition-colors {{ request()->routeIs('customer.dashboard') ? 'active' : 'text-slate-500 hover:text-slate-800' }}">
                Dashboard
            </a>
            <a href="{{ route('customer.bookings.index') }}"
               class="nav-link px-4 py-5 text-sm border-b-2 border-transparent transition-colors {{ request()->routeIs('customer.bookings*') ? 'active' : 'text-slate-500 hover:text-slate-800' }}">
                Booking
            </a>
            <a href="{{ route('customer.vehicles.index') }}"
               class="nav-link px-4 py-5 text-sm border-b-2 border-transparent transition-colors {{ request()->routeIs('customer.vehicles*') ? 'active' : 'text-slate-500 hover:text-slate-800' }}">
                Kendaraan Saya
            </a>
        </nav>

        {{-- Account dropdown --}}
        <div class="relative">
            <button id="customerAccountBtn" class="flex items-center gap-2.5 hover:bg-slate-50 px-3 py-2 rounded-xl transition-colors">
                <div class="w-8 h-8 rounded-full bg-slate-900 overflow-hidden flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                    @if(auth()->user()->photo)
                        <img src="{{ asset('storage/' . auth()->user()->photo) }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                    @endif
                </div>
                <span class="text-sm font-medium text-slate-700">{{ auth()->user()->name }}</span>
                <i id="customerChevron" class="fas fa-chevron-down text-xs text-slate-400 transition-transform"></i>
            </button>

            <div id="customerAccountPanel"
                 class="hidden absolute right-0 top-full mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-900 overflow-hidden flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                            @if(auth()->user()->photo)
                                <img src="{{ asset('storage/' . auth()->user()->photo) }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-sm text-slate-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-2">
                    <a href="{{ route('customer.profile') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                        <i class="fas fa-user-circle w-4 text-center text-slate-400"></i> Profil Saya
                    </a>
                    <a href="{{ route('customer.vehicles.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                        <i class="fas fa-car w-4 text-center text-slate-400"></i> Kendaraan Saya
                    </a>
                    <a href="{{ route('customer.bookings.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                        <i class="fas fa-calendar-check w-4 text-center text-slate-400"></i> Booking Saya
                    </a>
                    <div class="border-t border-slate-100 mt-1 pt-1">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm text-red-600 hover:bg-red-50 transition-colors font-medium">
                                <i class="fas fa-sign-out-alt w-4 text-center"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="flex-1 max-w-6xl mx-auto w-full px-6 py-8">
    @yield('content')
</main>
<x-modals.image-preview />
@stack('scripts')
<div id="loadingOverlay" class="hidden fixed inset-0 bg-black/40 backdrop-blur-md z-[9999] flex items-center justify-center">
    <div class="bg-white border border-slate-100 rounded-3xl shadow-2xl p-10 text-center animate-in fade-in zoom-in duration-300">
        <div class="w-12 h-12 rounded-full animate-spin mx-auto mb-5" style="border:4px solid #e2e8f0;border-top-color:#0f172a;"></div>
        <p class="font-bold text-base text-slate-900 font-display">Menghubungkan...</p>
        <p class="text-xs text-slate-400 mt-2 uppercase tracking-widest font-semibold">Mohon tunggu sebentar</p>
    </div>
</div>
</body>
</html>
