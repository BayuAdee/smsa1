@extends('layouts.app')

@section('title', 'Data Pengukuran Bulanan')
@section('page-title', 'Riwayat Pengukuran Bulanan')

@section('content')

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-extrabold text-white">Seluruh Catatan Pengukuran Bulanan</h2>
            <p class="text-xs text-slate-400 mt-0.5">Daftar pemeriksaan tinggi dan berat badan anak di Posyandu</p>
        </div>
        <a href="{{ route('pengukuran.create') }}" class="py-2.5 px-4 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-extrabold rounded-2xl text-xs flex items-center justify-center gap-2 min-h-[44px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Input Pengukuran Baru</span>
        </a>
    </div>

    @if($pengukurans->count() > 0)
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4 rounded-l-xl">Tanggal Ukur</th>
                        <th class="py-3 px-4">Nama Balita</th>
                        <th class="py-3 px-4">Posyandu</th>
                        <th class="py-3 px-4">Usia</th>
                        <th class="py-3 px-4">Tinggi Badan</th>
                        <th class="py-3 px-4">Berat Badan</th>
                        <th class="py-3 px-4 rounded-r-xl">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-200">
                    @foreach($pengukurans as $p)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-white">{{ \Carbon\Carbon::parse($p->tanggal_ukur)->translatedFormat('d M Y') }}</td>
                            <td class="py-3.5 px-4 font-bold text-white">
                                <a href="{{ route('balita.show', $p->anak->id) }}" class="hover:text-emerald-400">
                                    {{ $p->anak->nama }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">{{ $p->anak->posyandu->nama ?? '-' }}</td>
                            <td class="py-3.5 px-4">{{ $p->usia_bulan }} bulan</td>
                            <td class="py-3.5 px-4 font-bold text-emerald-400">{{ number_format($p->tinggi_cm, 1) }} cm</td>
                            <td class="py-3.5 px-4 font-bold text-teal-400">{{ number_format($p->berat_kg, 1) }} kg</td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $p->pembuat->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Stacked Card View -->
        <div class="md:hidden space-y-3">
            @foreach($pengukurans as $p)
                <div class="bg-slate-950/80 p-4 rounded-2xl border border-slate-800 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('balita.show', $p->anak->id) }}" class="font-bold text-white text-sm hover:text-emerald-400">
                            {{ $p->anak->nama }}
                        </a>
                        <span class="text-slate-400 text-[11px] font-semibold">{{ \Carbon\Carbon::parse($p->tanggal_ukur)->translatedFormat('d M Y') }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-800">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Tinggi Badan:</span>
                            <span class="font-bold text-emerald-400">{{ number_format($p->tinggi_cm, 1) }} cm</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Berat Badan:</span>
                            <span class="font-bold text-teal-400">{{ number_format($p->berat_kg, 1) }} kg</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Usia Ukur:</span>
                            <span class="font-semibold text-slate-200">{{ $p->usia_bulan }} bulan</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Posyandu:</span>
                            <span class="font-semibold text-slate-200">{{ $p->anak->posyandu->nama ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4 border-t border-slate-800">
            {{ $pengukurans->links() }}
        </div>
    @else
        <div class="text-center py-12 text-slate-400 text-xs">
            Belum ada catatan pengukuran bulanan.
        </div>
    @endif
</div>

@endsection
