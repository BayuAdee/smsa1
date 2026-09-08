@extends('layouts.app')

@section('title', 'Ranking Prioritas Stunting SAW')
@section('page-title', 'Hasil SPK Metode SAW - Prioritas Stunting')

@section('content')

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl dark:shadow-black/40 space-y-6 transition-colors duration-200">

    <!-- Header, Periode Dropdown, & Recalculate Button -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Month/Year SAW Engine</span>
            <h2 class="text-base sm:text-xl font-extrabold text-slate-900 dark:text-white mt-0.5">Ranking Prioritas Penanganan Stunting (SAW)</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl">
                SPK SAW COST dihitung per periode bulan: <strong class="text-emerald-600 dark:text-emerald-400">C1: TB/U Z-Score (40%)</strong>, <strong class="text-teal-600 dark:text-teal-400">C2: Growth Faltering (25%)</strong>, <strong class="text-cyan-600 dark:text-cyan-400">C3: BB/U Z-Score (25%)</strong>, dan <strong class="text-amber-600 dark:text-amber-400">C4: Riwayat BBLR (10%)</strong>.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <!-- Filter Periode & Kategori Dropdown -->
            <form action="{{ route('ranking.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="relative w-full sm:w-auto min-w-[170px]">
                    <label for="ranking_periode_select" class="sr-only">Pilih Periode Ranking</label>
                    <select id="ranking_periode_select" name="periode_key" onchange="submitRankingPeriode(this)" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-extrabold text-xs rounded-2xl px-4 py-2.5 appearance-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none cursor-pointer pr-10 shadow-inner min-h-[44px]">
                        @foreach($periodeOptions as $opt)
                            @php
                                $isSelected = ($opt['bulan'] === $selectedBulan && $opt['tahun'] === $selectedTahun);
                            @endphp
                            <option value="{{ $opt['bulan'] }}-{{ $opt['tahun'] }}" {{ $isSelected ? 'selected' : '' }}>
                                Periode: {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 dark:text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Filter Kategori Dropdown -->
                <div class="relative w-full sm:w-auto min-w-[150px]">
                    <label for="ranking_kategori_select" class="sr-only">Filter Kategori Risiko</label>
                    <select id="ranking_kategori_select" name="kategori" onchange="this.form.submit()" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-extrabold text-xs rounded-2xl px-4 py-2.5 appearance-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none cursor-pointer pr-10 shadow-inner min-h-[44px]">
                        <option value="all" {{ ($selectedKategori ?? 'all') === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                        <option value="tinggi" {{ ($selectedKategori ?? '') === 'tinggi' ? 'selected' : '' }}>Tinggi (Merah)</option>
                        <option value="sedang" {{ ($selectedKategori ?? '') === 'sedang' ? 'selected' : '' }}>Sedang (Kuning)</option>
                        <option value="rendah" {{ ($selectedKategori ?? '') === 'rendah' ? 'selected' : '' }}>Rendah (Hijau)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 dark:text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <input type="hidden" name="bulan" id="r_param_bulan" value="{{ $selectedBulan }}">
                <input type="hidden" name="tahun" id="r_param_tahun" value="{{ $selectedTahun }}">
            </form>

            <!-- Recalculate Button -->
            <form action="{{ route('ranking.recalculate') }}" method="POST">
                @csrf
                <input type="hidden" name="bulan" value="{{ $selectedBulan }}">
                <input type="hidden" name="tahun" value="{{ $selectedTahun }}">
                <button type="submit" class="w-full sm:w-auto py-2.5 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-emerald-700 dark:text-emerald-400 border border-slate-300 dark:border-emerald-500/30 font-extrabold rounded-2xl text-xs flex items-center justify-center gap-2 transition-all min-h-[44px] shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Hitung Ulang SPK</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Active Periode Status Banner -->
    <div class="p-4 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="space-y-1">
            <div class="font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span>
                <span>Periode Aktif: {{ \Carbon\Carbon::createFromDate($selectedTahun, $selectedBulan, 1)->translatedFormat('F Y') }}</span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-[11px]">
                Menampilkan hasil kalkulasi SPK SAW yang membandingkan balita dengan catatan pengukuran aktif pada periode ini.
            </p>
        </div>

        <span class="px-3 py-1.5 rounded-xl bg-slate-200 dark:bg-slate-800 text-amber-800 dark:text-amber-300 font-extrabold text-[11px] border border-slate-300 dark:border-slate-700 shrink-0">
            Total Matriks: {{ $rankings->count() }} Balita
        </span>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($rankings->count() > 0)
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px] font-extrabold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 text-center">Rank</th>
                        <th class="py-3.5 px-4">Nama Balita</th>
                        <th class="py-3.5 px-4">Posyandu</th>
                        <th class="py-3.5 px-4 text-center">C1 (TB/U)</th>
                        <th class="py-3.5 px-4 text-center">C2 (KBM)</th>
                        <th class="py-3.5 px-4 text-center">C3 (BB/U)</th>
                        <th class="py-3.5 px-4 text-center">C4 (BBLR)</th>
                        <th class="py-3.5 px-4 text-center font-bold text-amber-700 dark:text-amber-300">Nilai V_i</th>
                        <th class="py-3.5 px-4 text-center">Kategori Risiko</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200 font-medium">
                    @foreach($rankings as $index => $h)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                            <!-- Rank Number -->
                            <td class="py-4 px-4 text-center font-black text-sm">
                                @if($index === 0)
                                    <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-rose-500 to-amber-500 text-slate-950 inline-flex items-center justify-center font-black shadow-md">#1</span>
                                @elseif($index === 1)
                                    <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-400 to-yellow-400 text-slate-950 inline-flex items-center justify-center font-black shadow-md">#2</span>
                                @elseif($index === 2)
                                    <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-400 text-slate-950 inline-flex items-center justify-center font-black shadow-md">#3</span>
                                @else
                                    <span class="text-slate-500 dark:text-slate-400">#{{ $index + 1 }}</span>
                                @endif
                            </td>

                            <!-- Child Info -->
                            <td class="py-4 px-4 font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('balita.show', $h->anak->id) }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                    {{ $h->anak->nama }}
                                </a>
                            </td>

                            <td class="py-4 px-4 text-slate-600 dark:text-slate-300">{{ $h->anak->posyandu->nama ?? '-' }}</td>

                            <!-- C1 TB/U -->
                            <td class="py-4 px-4 text-center">
                                <span class="block font-bold {{ $h->z_tbu < -2.0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ number_format($h->z_tbu, 2) }} SD</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">r1: {{ number_format($h->r_c1, 3) }}</span>
                            </td>

                            <!-- C2 Growth Faltering -->
                            <td class="py-4 px-4 text-center">
                                <span class="block font-bold text-slate-800 dark:text-slate-200">Skor {{ (int)$h->raw_c2 }}</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">r2: {{ number_format($h->r_c2, 3) }}</span>
                                @if($h->is_c2_estimasi)
                                    <span class="block text-[9px] text-amber-600 dark:text-amber-400 font-semibold">(Estimasi)</span>
                                @endif
                            </td>

                            <!-- C3 BB/U -->
                            <td class="py-4 px-4 text-center">
                                <span class="block font-bold text-teal-600 dark:text-teal-400">{{ number_format($h->z_bbu, 2) }} SD</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">r3: {{ number_format($h->r_c3, 3) }}</span>
                            </td>

                            <!-- C4 BBLR -->
                            <td class="py-4 px-4 text-center">
                                <span class="block font-bold text-slate-800 dark:text-slate-200">Skor {{ (int)$h->raw_c4 }}</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">r4: {{ number_format($h->r_c4, 3) }}</span>
                            </td>

                            <!-- Nilai V -->
                            <td class="py-4 px-4 text-center font-mono font-black text-amber-700 dark:text-amber-300 text-sm">
                                {{ number_format($h->nilai_v, 4) }}
                            </td>

                            <!-- Kategori Risiko Badge -->
                            <td class="py-4 px-4 text-center">
                                @php
                                    $kat = strtolower($h->kategori_risiko);
                                @endphp
                                @if(in_array($kat, ['tinggi', 'sangat tinggi', 'sangat_tinggi']))
                                    <span class="px-3 py-1 rounded-full text-[11px] font-black bg-rose-500/10 dark:bg-rose-500/20 text-rose-800 dark:text-rose-300 border border-rose-500/30">
                                        Tinggi
                                    </span>
                                @elseif($kat === 'sedang')
                                    <span class="px-3 py-1 rounded-full text-[11px] font-black bg-amber-500/10 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-500/30">
                                        Sedang
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-[11px] font-black bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30">
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
                <div class="bg-slate-50/90 dark:bg-slate-950/80 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-slate-200 dark:bg-slate-800 font-extrabold text-xs text-amber-700 dark:text-amber-400 flex items-center justify-center border border-slate-300 dark:border-slate-700">#{{ $index + 1 }}</span>
                            <div>
                                <a href="{{ route('balita.show', $h->anak->id) }}" class="font-bold text-slate-900 dark:text-white text-sm hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                    {{ $h->anak->nama }}
                                </a>
                                <span class="block text-[10px] text-slate-500 dark:text-slate-400">{{ $h->anak->posyandu->nama ?? '-' }}</span>
                            </div>
                        </div>

                        @php
                            $katMob = strtolower($h->kategori_risiko);
                        @endphp
                        @if(in_array($katMob, ['tinggi', 'sangat tinggi', 'sangat_tinggi']))
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-500/10 dark:bg-rose-500/20 text-rose-800 dark:text-rose-300 border border-rose-500/30">
                                Tinggi
                            </span>
                        @elseif($katMob === 'sedang')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-500/10 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-500/30">
                                Sedang
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30">
                                Rendah
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-200 dark:border-slate-800/80">
                        <div class="bg-white dark:bg-slate-900/60 p-2 rounded-xl border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400 block text-[10px]">C1 (TB/U Z-Score):</span>
                            <span class="font-bold {{ $h->z_tbu < -2.0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ number_format($h->z_tbu, 2) }} SD</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono block">r1: {{ number_format($h->r_c1, 3) }}</span>
                        </div>
                        <div class="bg-white dark:bg-slate-900/60 p-2 rounded-xl border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400 block text-[10px]">C2 (Growth Faltering):</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">Skor {{ (int)$h->raw_c2 }}</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono block">r2: {{ number_format($h->r_c2, 3) }}</span>
                        </div>
                        <div class="bg-white dark:bg-slate-900/60 p-2 rounded-xl border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400 block text-[10px]">C3 (BB/U Z-Score):</span>
                            <span class="font-bold text-teal-600 dark:text-teal-400">{{ number_format($h->z_bbu, 2) }} SD</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono block">r3: {{ number_format($h->r_c3, 3) }}</span>
                        </div>
                        <div class="bg-white dark:bg-slate-900/60 p-2 rounded-xl border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400 block text-[10px]">Nilai Preferensi V_i:</span>
                            <span class="font-mono font-black text-amber-700 dark:text-amber-300 text-sm">{{ number_format($h->nilai_v, 4) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 text-slate-500 dark:text-slate-400 text-xs">
            Belum ada data perhitungan ranking SAW untuk periode <strong>{{ \Carbon\Carbon::createFromDate($selectedTahun, $selectedBulan, 1)->translatedFormat('F Y') }}</strong>. Silakan input pengukuran balita atau klik tombol "Hitung Ulang SPK".
        </div>
    @endif

</div>

<script>
function submitRankingPeriode(selectEl) {
    const val = selectEl.value;
    const parts = val.split('-');
    if (parts.length === 2) {
        document.getElementById('r_param_bulan').value = parts[0];
        document.getElementById('r_param_tahun').value = parts[1];
        selectEl.form.submit();
    }
}
</script>

@endsection
