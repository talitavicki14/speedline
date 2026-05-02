@extends('layouts.admin')
@section('title','Pengguna')
@section('page-title','Pengguna')

@section('content')
@can('manage-data')
<div class="flex justify-end mb-5">
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
        <i class="fas fa-plus text-xs"></i> Tambah Pengguna
    </a>
</div>
@endcan

<div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-6 pt-4 border-b border-slate-100">
        <div class="flex gap-0" id="tabs-container">
            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['tab'=>'internal'])) }}"
               class="px-4 py-3 text-sm font-semibold border-b-2 transition-colors -mb-px
                      {{ $tab === 'internal' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-700' }}">
                Internal
                <span class="ml-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full
                             {{ $tab === 'internal' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500' }}">
                    {{ $internalUsers->total() }}
                </span>
            </a>
            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['tab'=>'customer'])) }}"
               class="px-4 py-3 text-sm font-semibold border-b-2 transition-colors -mb-px
                      {{ $tab === 'customer' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-700' }}">
                Pelanggan
                <span class="ml-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full
                             {{ $tab === 'customer' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500' }}">
                    {{ $customers->total() }}
                </span>
            </a>
        </div>

        <form method="GET" class="flex items-center gap-2 pb-3" data-auto-filter>
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..."
                   class="border border-slate-200 focus:border-slate-400 rounded-lg px-3 py-2 text-sm outline-none w-44 placeholder-slate-300 text-slate-700">
            @if($tab === 'internal')
            <div data-custom-select
                 data-name="role"
                 data-placeholder="Semua Peran"
                 data-value="{{ request('role') }}"
                 data-options='[{"value":"admin","label":"Admin"},{"value":"owner","label":"Pemilik"},{"value":"mekanik","label":"Mekanik"},{"value":"kasir","label":"Kasir"}]'
                 class="w-36"></div>
            @endif

            <div id="clear-container">
            @if(request()->hasAny(['search','role']))
            <a href="{{ route('admin.users.index', ['tab'=>$tab]) }}"
               class="text-sm text-slate-500 hover:text-slate-800 px-3 py-2 border border-slate-200 rounded-lg transition-colors">Hapus</a>
            @endif
            </div>
        </form>
    </div>

    <div id="filter-container">

    {{-- ==================== INTERNAL TAB ==================== --}}
    @if($tab === 'internal')
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="bg-slate-50">
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Nama</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Email</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Telepon</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Peran</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Terdaftar</th>
                <th class="px-6 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($internalUsers as $u)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($u->photo)
                                <img src="{{ asset('storage/' . $u->photo) }}" 
                                     class="w-8 h-8 rounded-full object-cover cursor-pointer hover:ring-2 hover:ring-slate-300 transition-all"
                                     onclick="showImageFull(this.src)">
                            @else
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-semibold text-xs text-slate-600 flex-shrink-0">
                                    {{ strtoupper(substr($u->name,0,1)) }}
                                </div>
                            @endif
                            <span class="font-medium text-sm text-slate-800">{{ $u->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $u->email }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $u->phone ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <x-badges.role :role="$u->role" />
                            @if($u->trashed())
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">Nonaktif</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-400">{{ $u->created_at->translatedFormat('d M Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('manage-data')
                                @can('manage', $u)
                                    @if($u->trashed())
                                    <form action="{{ route('admin.users.restore', $u->id) }}" method="POST" class="inline" data-confirm="Aktifkan kembali pengguna ini?">
                                        @csrf
                                        <button class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 border border-emerald-200 hover:border-emerald-400 px-3 py-1.5 rounded-lg transition-colors">Aktifkan</button>
                                    </form>
                                    @else
                                    <a href="{{ route('admin.users.edit', $u) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">Edit</a>
                                    @if(auth()->id() !== $u->id)
                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline" data-confirm="Nonaktifkan pengguna ini?">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-red-500 hover:text-red-700 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg transition-colors">Nonaktifkan</button>
                                    </form>
                                    @endif
                                    @endif
                                @else
                                    <span class="text-[10px] font-semibold text-slate-400 italic">Dilindungi</span>
                                @endcan
                            @else
                                <span class="text-[10px] text-slate-300 italic">Hanya Baca</span>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-20">
                        <div class="flex flex-col items-center justify-center text-center">
                            <i class="fas fa-users-cog text-5xl mb-4 text-slate-200"></i>
                            <p class="text-sm text-slate-400 font-medium tracking-wide">Tidak ada staf ditemukan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination footer --}}
    <x-ui.table-footer :paginator="$internalUsers" :except="['page', 'per_page', 'tab']" :append="['tab' => 'internal']" />
    @endif

    {{-- ==================== CUSTOMERS TAB ==================== --}}
    @if($tab === 'customer')
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="bg-slate-50">
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Nama</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Email</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Telepon</th>
                <th class="text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-6 py-3">Terdaftar</th>
                <th class="px-6 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($customers as $u)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($u->photo)
                                <img src="{{ asset('storage/' . $u->photo) }}" 
                                     class="w-8 h-8 rounded-full object-cover cursor-pointer hover:ring-2 hover:ring-slate-300 transition-all"
                                     onclick="showImageFull(this.src)">
                            @else
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-semibold text-xs text-slate-600 flex-shrink-0">
                                    {{ strtoupper(substr($u->name,0,1)) }}
                                </div>
                            @endif
                            <span class="font-medium text-sm text-slate-800">{{ $u->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $u->email }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        <div class="flex items-center gap-2">
                            {{ $u->phone ?? '—' }}
                            @if($u->trashed())
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">Nonaktif</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-400">{{ $u->created_at->translatedFormat('d M Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('manage-data')
                                @can('manage', $u)
                                    @if($u->trashed())
                                    <form action="{{ route('admin.users.restore', $u->id) }}" method="POST" class="inline" data-confirm="Aktifkan kembali pelanggan ini?">
                                        @csrf
                                        <button class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 border border-emerald-200 hover:border-emerald-400 px-3 py-1.5 rounded-lg transition-colors">Aktifkan</button>
                                    </form>
                                    @else
                                    <a href="{{ route('admin.users.edit', $u) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">Edit</a>
                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline" data-confirm="Nonaktifkan pelanggan ini?">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-red-500 hover:text-red-700 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg transition-colors">Nonaktifkan</button>
                                    </form>
                                    @endif
                                @else
                                    <span class="text-[10px] font-semibold text-slate-400 italic">Dilindungi</span>
                                @endcan
                            @else
                                <span class="text-[10px] text-slate-300 italic">Hanya Baca</span>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-20">
                        <div class="flex flex-col items-center justify-center text-center">
                            <i class="fas fa-users text-5xl mb-4 text-slate-200"></i>
                            <p class="text-sm text-slate-400 font-medium tracking-wide">Tidak ada pelanggan ditemukan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination footer --}}
    <x-ui.table-footer :paginator="$customers" :except="['page', 'per_page', 'tab']" :append="['tab' => 'customer']" />
    @endif

    </div>
</div>
@endsection
