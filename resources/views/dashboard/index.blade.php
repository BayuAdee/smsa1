@extends('layouts.app')

@section('title', 'Dashboard Utama')
@section('page-title', 'Dashboard Utama Monitoring')

@section('content')

<div class="space-y-6">

    <!-- Dashboard Top Bar & Periode Filter -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-black text-white">Ringkasan Sistem & Perangkingan</h2>
            <p class="text-xs text-slate-400 mt-0.5">Monitoring stunting dan hasil SPK SAW berdasarkan periode bulan.</p>
        </div>

        <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2">
            <div class="relative w-full sm:w-auto min-w-[200px]">
                <label for="dash_periode_select" class="sr-only">Pilih Periode Dashboard</label>
                <select id="dash_periode_select" name="periode_key" onchange="submitDashPeriode(this)" class="w-full bg-slate-950 border border-slate-700 text-white font-extrabold text-xs rounded-2xl px-4 py-2.5 appearance-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer pr-10 shadow-inner min-h-[44px]">
                    @foreach($periodeOptions as $opt)
                        @php
                            $isSelected = ($opt['bulan'] === $selectedBulan && $opt['tahun'] === $selectedTahun);
                        @endphp
                        <option value="{{ $opt['bulan'] }}-{{ $opt['tahun'] }}" {{ $isSelected ? 'selected' : '' }}>
                            Periode: {{ $opt['label'] }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
            <input type="hidden" name="bulan" id="d_param_bulan" value="{{ $selectedBulan }}">
            <input type="hidden" name="tahun" id="d_param_tahun" value="{{ $selectedTahun }}">
        </form>
    </div>

    <!-- Stat Cards (1 Col Mobile, 3 Cols Desktop) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- Card 1: Total Balita -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl relative overflow-hidden group hover:border-emerald-500/30 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <span class="block text-xs font-extrabold uppercase tracking-wider text-slate-400">Total Balita Terdaftar</span>
                    <span class="text-3xl sm:text-4xl font-black text-white mt-1.5 block tracking-tight">{{ number_format($totalBalita) }}</span>
                    <span class="text-[11px] text-slate-400 mt-1 block">Aktif di sistem Posyandu</span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shadow-inner group-hover:scale-110 transition-transform shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Card 2: Balita Berisiko Tinggi/Sangat Tinggi -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl relative overflow-hidden group hover:border-rose-500/30 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <span class="block text-xs font-extrabold uppercase tracking-wider text-rose-300">Risiko Tinggi / Sangat Tinggi</span>
                    <span class="text-3xl sm:text-4xl font-black text-rose-400 mt-1.5 block tracking-tight">{{ number_format($totalRisikoTinggi) }}</span>
                    <span class="text-[11px] text-slate-400 mt-1 block">Periode {{ \Carbon\Carbon::createFromDate($selectedTahun, $selectedBulan, 1)->translatedFormat('F Y') }}</span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-400 shadow-inner group-hover:scale-110 transition-transform shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Pengukuran Periode -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl relative overflow-hidden group hover:border-teal-500/30 transition-all duration-300 sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <div>
                    <span class="block text-xs font-extrabold uppercase tracking-wider text-slate-400">Pengukuran Periode Ini</span>
                    <span class="text-3xl sm:text-4xl font-black text-teal-300 mt-1.5 block tracking-tight">{{ number_format($totalPengukuranBulanIni) }}</span>
                    <span class="text-[11px] text-slate-400 mt-1 block">Catatan {{ \Carbon\Carbon::createFromDate($selectedTahun, $selectedBulan, 1)->translatedFormat('F Y') }}</span>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-teal-500/10 border border-teal-500/30 flex items-center justify-center text-teal-400 shadow-inner group-hover:scale-110 transition-transform shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Shortcuts Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <a href="{{ route('pengukuran.index', ['bulan' => $selectedBulan, 'tahun' => $selectedTahun]) }}" class="p-4 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-extrabold rounded-2xl shadow-lg hover:shadow-emerald-500/20 active:scale-95 transition-all flex items-center justify-between gap-3 min-h-[52px]">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Input Pengukuran Bulanan</span>
            </div>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <a href="{{ route('balita.create') }}" class="p-4 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-white font-extrabold rounded-2xl active:scale-95 transition-all flex items-center justify-between gap-3 min-h-[52px]">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span>Tambah Data Balita</span>
            </div>
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <a href="{{ route('ranking.index', ['bulan' => $selectedBulan, 'tahun' => $selectedTahun]) }}" class="p-4 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-white font-extrabold rounded-2xl active:scale-95 transition-all flex items-center justify-between gap-3 min-h-[52px]">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span>Lihat Ranking Prioritas SAW</span>
            </div>
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <!-- Top Priority Stunting Table / Mobile Cards -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="text-base sm:text-lg font-extrabold text-white">Prioritas Penanganan Stunting Teratas (Hasil SAW)</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Periode: <strong>{{ \Carbon\Carbon::createFromDate($selectedTahun, $selectedBulan, 1)->translatedFormat('F Y') }}</strong> • Nilai Preferensi V terkecil (Risiko Stunting Tertinggi)
                </p>
            </div>
            <a href="{{ route('ranking.index', ['bulan' => $selectedBulan, 'tahun' => $selectedTahun]) }}" class="text-xs font-bold text-emerald-400 hover:text-emerald-300 inline-flex items-center gap-1 self-start sm:self-auto">
                <span>Lihat Seluruh Ranking</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        @if($topRisiko->count() > 0)
            <!-- Desktop Table View (md: breakpoint up) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-950 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4 rounded-l-xl">Rank</th>
                            <th class="py-3 px-4">Nama Balita</th>
                            <th class="py-3 px-4">Posyandu</th>
                            <th class="py-3 px-4">Usia / TB / BB</th>
                            <th class="py-3 px-4">Z-Score TB/U</th>
                            <th class="py-3 px-4">Nilai V (SAW)</th>
                            <th class="py-3 px-4 rounded-r-xl">Kategori Risiko</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-200">
                        @foreach($topRisiko as $index => $h)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 font-black text-slate-400 text-sm">#{{ $index + 1 }}</td>
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('balita.show', $h->anak->id) }}" class="font-bold text-white hover:text-emerald-400 transition-colors block">
                                        {{ $h->anak->nama }}
                                    </a>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[11px] text-slate-400 font-mono">{{ $h->anak->token_akses }}</span>
                                        <button type="button" onclick="copyToClipboard('{{ $h->anak->token_akses }}', this)" title="Salin Token Akses" class="text-slate-500 hover:text-emerald-400 transition-colors p-0.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 002-2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-300">{{ $h->anak->posyandu->nama ?? '-' }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="block font-semibold">{{ $h->pengukuran->usia_bulan ?? '-' }} Bulan</span>
                                    <span class="text-slate-400 text-[11px]">{{ $h->pengukuran->tinggi_cm ?? '-' }} cm | {{ $h->pengukuran->berat_kg ?? '-' }} kg</span>
                                </td>
                                <td class="py-3.5 px-4 font-bold {{ $h->z_tbu < -2.0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                    {{ number_format($h->z_tbu, 2) }} SD
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-amber-300">
                                    {{ number_format($h->nilai_v, 4) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($h->kategori_risiko === 'Sangat Tinggi')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                            Sangat Tinggi
                                        </span>
                                    @elseif($h->kategori_risiko === 'Tinggi')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            Tinggi
                                        </span>
                                    @elseif($h->kategori_risiko === 'Sedang')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                                            Sedang
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            Rendah
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Stacked Card View (under md breakpoint) -->
            <div class="md:hidden space-y-3">
                @foreach($topRisiko as $index => $h)
                    <div class="bg-slate-950/80 p-4 rounded-2xl border border-slate-800 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-slate-800 font-extrabold text-xs text-amber-400 flex items-center justify-center">#{{ $index + 1 }}</span>
                                <a href="{{ route('balita.show', $h->anak->id) }}" class="font-bold text-white text-sm hover:text-emerald-400">
                                    {{ $h->anak->nama }}
                                </a>
                            </div>
                            @if($h->kategori_risiko === 'Sangat Tinggi')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                    Sangat Tinggi
                                </span>
                            @elseif($h->kategori_risiko === 'Tinggi')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    Tinggi
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    {{ $h->kategori_risiko }}
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-slate-800/80">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Posyandu:</span>
                                <span class="font-semibold text-slate-200">{{ $h->anak->posyandu->nama ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Nilai V (SAW):</span>
                                <span class="font-mono font-bold text-amber-300">{{ number_format($h->nilai_v, 4) }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Fisik:</span>
                                <span class="font-semibold text-slate-200">{{ $h->pengukuran->tinggi_cm ?? '-' }}cm | {{ $h->pengukuran->berat_kg ?? '-' }}kg</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Z-Score TB/U:</span>
                                <span class="font-bold {{ $h->z_tbu < -2.0 ? 'text-rose-400' : 'text-emerald-400' }}">{{ number_format($h->z_tbu, 2) }} SD</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-slate-400 text-xs">
                Belum ada data hasil perhitungan SPK SAW untuk periode <strong>{{ \Carbon\Carbon::createFromDate($selectedTahun, $selectedBulan, 1)->translatedFormat('F Y') }}</strong>.
            </div>
        @endif
    </div>

</div>

<script>
function submitDashPeriode(selectEl) {
    const val = selectEl.value;
    const parts = val.split('-');
    if (parts.length === 2) {
        document.getElementById('d_param_bulan').value = parts[0];
        document.getElementById('d_param_tahun').value = parts[1];
        selectEl.form.submit();
    }
}
</script>

@endsection
