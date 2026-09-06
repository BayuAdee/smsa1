@extends('layouts.app')

@section('title', 'Ranking Prioritas Stunting SAW')
@section('page-title', 'Hasil SPK Metode SAW - Prioritas Stunting')

@section('content')

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-6">
    
    <!-- Header & Recalculate Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-xl font-extrabold text-white">Ranking Prioritas Penanganan Stunting (Simple Additive Weighting)</h2>
            <p class="text-xs text-slate-400 mt-1 max-w-2xl">
                Penetapan prioritas dihitung secara otomatis berdasarkan 4 kriteria COST: <strong class="text-emerald-400">C1: TB/U Z-Score (40%)</strong>, <strong class="text-teal-400">C2: Growth Faltering (25%)</strong>, <strong class="text-cyan-400">C3: BB/U Z-Score (25%)</strong>, dan <strong class="text-amber-400">C4: Riwayat BBLR (10%)</strong>.
            </p>
        </div>

        <form action="{{ route('ranking.recalculate') }}" method="POST">
            @csrf
            <button type="submit" class="w-full sm:w-auto py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-emerald-500/30 font-extrabold rounded-2xl text-xs flex items-center justify-center gap-2 transition-all min-h-[44px] shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Hitung Ulang SPK SAW</span>
            </button>
        </form>
    </div>

    <!-- Explanation Banner -->
    <div class="p-4 bg-slate-950/80 border border-slate-800 rounded-2xl text-xs space-y-2">
        <div class="font-bold text-white flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Metode Perhitungan Cost Normalization:</span>
        </div>
        <p class="text-slate-300 text-[11px] leading-relaxed">
            Dalam metode SAW COST, rumus normalisasi yang digunakan adalah <code class="text-emerald-300">r_ij = min(X_j) / X_ij</code>.
            Balita dengan <strong class="text-rose-300">Nilai Preferensi V_i terkecil</strong> menunjukkan derajat risiko stunting tertinggi (Ranking #1) dan memerlukan prioritas intervensi penanganan paling mendesak.
        </p>
    </div>

    @if($rankings->count() > 0)
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4 rounded-l-xl text-center">Rank</th>
                        <th class="py-3.5 px-4">Nama Balita</th>
                        <th class="py-3.5 px-4">Posyandu</th>
                        <th class="py-3.5 px-4 text-center">C1 (TB/U)</th>
                        <th class="py-3.5 px-4 text-center">C2 (KBM)</th>
                        <th class="py-3.5 px-4 text-center">C3 (BB/U)</th>
                        <th class="py-3.5 px-4 text-center">C4 (BBLR)</th>
                        <th class="py-3.5 px-4 text-center font-bold text-amber-300">Nilai V_i</th>
                        <th class="py-3.5 px-4 rounded-r-xl text-center">Kategori Risiko</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-200">
                    @foreach($rankings as $index => $h)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <!-- Rank Number -->
                            <td class="py-4 px-4 text-center font-black text-sm">
                                @if($index === 0)
                                    <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-rose-500 to-amber-500 text-slate-950 inline-flex items-center justify-center font-black shadow-md">#1</span>
                                @elseif($index === 1)
                                    <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-400 to-yellow-400 text-slate-950 inline-flex items-center justify-center font-black shadow-md">#2</span>
                                @elseif($index === 2)
                                    <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-400 text-slate-950 inline-flex items-center justify-center font-black shadow-md">#3</span>
                                @else
                                    <span class="text-slate-400">#{{ $index + 1 }}</span>
                                @endif
                            </td>

                            <!-- Child Info -->
                            <td class="py-4 px-4 font-bold text-white">
                                <a href="{{ route('balita.show', $h->anak->id) }}" class="hover:text-emerald-400">
                                    {{ $h->anak->nama }}
                                </a>
                                <span class="block text-[11px] text-slate-400 font-mono font-normal">{{ $h->anak->token_akses }}</span>
                            </td>

                            <td class="py-4 px-4 text-slate-300">{{ $h->anak->posyandu->nama ?? '-' }}</td>

                            <!-- C1 TB/U -->
                            <td class="py-4 px-4 text-center">
                                <span class="block font-bold {{ $h->z_tbu < -2.0 ? 'text-rose-400' : 'text-emerald-400' }}">{{ number_format($h->z_tbu, 2) }} SD</span>
                                <span class="text-[10px] text-slate-400 font-mono">r1: {{ number_format($h->r_c1, 3) }}</span>
                            </td>

                            <!-- C2 Growth Faltering -->
                            <td class="py-4 px-4 text-center">
                                <span class="block font-bold text-slate-200">Skor {{ (int)$h->raw_c2 }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">r2: {{ number_format($h->r_c2, 3) }}</span>
                                @if($h->is_c2_estimasi)
                                    <span class="block text-[9px] text-amber-400 font-semibold">(Estimasi)</span>
                                @endif
                            </td>

                            <!-- C3 BB/U -->
                            <td class="py-4 px-4 text-center">
                                <span class="block font-bold text-teal-400">{{ number_format($h->z_bbu, 2) }} SD</span>
                                <span class="text-[10px] text-slate-400 font-mono">r3: {{ number_format($h->r_c3, 3) }}</span>
                            </td>

                            <!-- C4 BBLR -->
                            <td class="py-4 px-4 text-center">
                                <span class="block font-bold text-slate-200">Skor {{ (int)$h->raw_c4 }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">r4: {{ number_format($h->r_c4, 3) }}</span>
                            </td>

                            <!-- Nilai V -->
                            <td class="py-4 px-4 text-center font-mono font-black text-amber-300 text-sm">
                                {{ number_format($h->nilai_v, 4) }}
                            </td>

                            <!-- Kategori Risiko Badge -->
                            <td class="py-4 px-4 text-center">
                                @if($h->kategori_risiko === 'Sangat Tinggi')
                                    <span class="px-3 py-1 rounded-full text-[11px] font-black bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                        Sangat Tinggi
                                    </span>
                                @elseif($h->kategori_risiko === 'Tinggi')
                                    <span class="px-3 py-1 rounded-full text-[11px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        Tinggi
                                    </span>
                                @elseif($h->kategori_risiko === 'Sedang')
                                    <span class="px-3 py-1 rounded-full text-[11px] font-black bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                                        Sedang
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-[11px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        Rendah
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Stacked Card View (under lg breakpoint) -->
        <div class="lg:hidden space-y-4">
            @foreach($rankings as $index => $h)
                <div class="bg-slate-950/80 p-4 rounded-2xl border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-slate-800 font-extrabold text-xs text-amber-400 flex items-center justify-center border border-slate-700">#{{ $index + 1 }}</span>
                            <div>
                                <a href="{{ route('balita.show', $h->anak->id) }}" class="font-bold text-white text-sm hover:text-emerald-400">
                                    {{ $h->anak->nama }}
                                </a>
                                <span class="block text-[10px] text-slate-400">{{ $h->anak->posyandu->nama ?? '-' }}</span>
                            </div>
                        </div>

                        @if($h->kategori_risiko === 'Sangat Tinggi')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                Sangat Tinggi
                            </span>
                        @elseif($h->kategori_risiko === 'Tinggi')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                Tinggi
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                {{ $h->kategori_risiko }}
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-800/80">
                        <div class="bg-slate-900/60 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400 block text-[10px]">C1 (TB/U Z-Score):</span>
                            <span class="font-bold {{ $h->z_tbu < -2.0 ? 'text-rose-400' : 'text-emerald-400' }}">{{ number_format($h->z_tbu, 2) }} SD</span>
                            <span class="text-[10px] text-slate-500 font-mono block">r1: {{ number_format($h->r_c1, 3) }}</span>
                        </div>
                        <div class="bg-slate-900/60 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400 block text-[10px]">C2 (Growth Faltering):</span>
                            <span class="font-bold text-slate-200">Skor {{ (int)$h->raw_c2 }}</span>
                            <span class="text-[10px] text-slate-500 font-mono block">r2: {{ number_format($h->r_c2, 3) }}</span>
                        </div>
                        <div class="bg-slate-900/60 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400 block text-[10px]">C3 (BB/U Z-Score):</span>
                            <span class="font-bold text-teal-400">{{ number_format($h->z_bbu, 2) }} SD</span>
                            <span class="text-[10px] text-slate-500 font-mono block">r3: {{ number_format($h->r_c3, 3) }}</span>
                        </div>
                        <div class="bg-slate-900/60 p-2 rounded-xl border border-slate-800">
                            <span class="text-slate-400 block text-[10px]">Nilai Preferensi V_i:</span>
                            <span class="font-mono font-black text-amber-300 text-sm">{{ number_format($h->nilai_v, 4) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 text-slate-400 text-xs">
            Belum ada data perhitungan ranking SAW. Silakan klik tombol "Hitung Ulang SPK SAW".
        </div>
    @endif

</div>

@endsection
