@extends('layouts.app')

@section('title', 'Data Balita')
@section('page-title', 'Manajemen Data Balita (Anak)')

@section('content')

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-5">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Search Box -->
        <form action="{{ route('balita.index') }}" method="GET" class="w-full sm:w-80">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" 
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-700 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 rounded-2xl text-white text-xs placeholder-slate-500"
                       placeholder="Cari nama, NIK, token, ortu...">
            </div>
        </form>

        <!-- Create Button -->
        <a href="{{ route('balita.create') }}" class="py-2.5 px-4 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-extrabold rounded-2xl shadow-md active:scale-95 transition-all text-xs flex items-center justify-center gap-2 min-h-[44px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Balita Baru</span>
        </a>
    </div>

    @if($anaks->count() > 0)
        <!-- Desktop Table View (md: up) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4 rounded-l-xl">Nama Balita</th>
                        <th class="py-3 px-4">Posyandu</th>
                        <th class="py-3 px-4">Tgl Lahir / Usia</th>
                        <th class="py-3 px-4">J.K / BBLR</th>
                        <th class="py-3 px-4">Orang Tua</th>
                        <th class="py-3 px-4">Token Ortu</th>
                        <th class="py-3 px-4 text-right rounded-r-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-200">
                    @foreach($anaks as $anak)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-white">
                                <a href="{{ route('balita.show', $anak->id) }}" class="hover:text-emerald-400">
                                    {{ $anak->nama }}
                                </a>
                                @if($anak->nik)
                                    <span class="block text-[11px] text-slate-400 font-normal">NIK: {{ $anak->nik }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">{{ $anak->posyandu->nama ?? '-' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="block font-medium">{{ $anak->tanggal_lahir->translatedFormat('d M Y') }}</span>
                                <span class="text-emerald-400 font-bold text-[11px]">{{ $anak->usia_bulan }} Bulan</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $anak->jenis_kelamin === 'L' ? 'bg-sky-500/20 text-sky-300' : 'bg-pink-500/20 text-pink-300' }}">
                                    {{ $anak->jenis_kelamin }}
                                </span>
                                @if($anak->status_bblr === 'bblr')
                                    <span class="block text-rose-400 text-[10px] font-semibold mt-1">BBLR (&lt;2500g)</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">{{ $anak->nama_orang_tua ?? '-' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 bg-slate-950 border border-slate-800 font-mono text-emerald-400 text-[11px] font-bold rounded-xl">
                                    {{ $anak->token_akses }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('pengukuran.create', ['anak_id' => $anak->id]) }}" title="Input Ukur" class="p-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 rounded-xl transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('balita.edit', $anak->id) }}" title="Edit" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List View (under md) -->
        <div class="md:hidden space-y-3">
            @foreach($anaks as $anak)
                <div class="bg-slate-950/80 p-4 rounded-2xl border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <a href="{{ route('balita.show', $anak->id) }}" class="font-bold text-white text-sm hover:text-emerald-400">
                                {{ $anak->nama }}
                            </a>
                            <span class="block text-[11px] text-slate-400">Usia: <strong class="text-emerald-400">{{ $anak->usia_bulan }} bln</strong></span>
                        </div>
                        <span class="px-2.5 py-1 bg-slate-900 border border-slate-700 font-mono text-emerald-400 text-[11px] font-bold rounded-xl">
                            {{ $anak->token_akses }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-800">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Posyandu:</span>
                            <span class="font-semibold text-slate-200">{{ $anak->posyandu->nama ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Orang Tua:</span>
                            <span class="font-semibold text-slate-200">{{ $anak->nama_orang_tua ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                        <a href="{{ route('pengukuran.create', ['anak_id' => $anak->id]) }}" class="px-3 py-1.5 bg-emerald-500/20 text-emerald-300 font-extrabold rounded-xl text-xs flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Input Ukur</span>
                        </a>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('balita.edit', $anak->id) }}" class="p-2 bg-slate-800 text-slate-300 rounded-xl text-xs font-semibold">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-4 border-t border-slate-800">
            {{ $anaks->links() }}
        </div>
    @else
        <div class="text-center py-12 text-slate-400 text-xs">
            Belum ada data balita yang ditemukan.
        </div>
    @endif
</div>

@endsection
