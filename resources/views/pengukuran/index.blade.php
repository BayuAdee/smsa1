@extends('layouts.app')

@section('title', 'Input Pengukuran Bulanan')
@section('page-title', 'Alur Input Pengukuran Bulanan (Period-Based)')

@section('content')

<div class="space-y-6">

    <!-- Header & Periode Selector Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl dark:shadow-black/40 space-y-4 transition-colors duration-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Posyandu Session</span>
                <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white mt-0.5">Input Pengukuran Fisik Bulanan</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pilih periode pengukuran untuk mencatat data tinggi & berat badan balita.</p>
            </div>

            <!-- Form Filter Periode -->
            <form action="{{ route('pengukuran.index') }}" method="GET" class="flex items-center gap-2">
                <div class="relative min-w-[200px]">
                    <label for="periode_select" class="sr-only">Pilih Periode Pengukuran</label>
                    <select id="periode_select" name="periode_key" onchange="submitPeriode(this)" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-extrabold text-xs rounded-2xl px-4 py-3 appearance-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer pr-10 shadow-inner">
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
                <!-- Hidden inputs for separate params -->
                <input type="hidden" name="bulan" id="param_bulan" value="{{ $selectedBulan }}">
                <input type="hidden" name="tahun" id="param_tahun" value="{{ $selectedTahun }}">
            </form>
        </div>

        <!-- Progress Summary Badge -->
        @php
            $totalBalita = $anaks->count();
            $sudahDiukur = $anaks->filter(fn($a) => !is_null($a->pengukuran_periode))->count();
            $percent = $totalBalita > 0 ? round(($sudahDiukur / $totalBalita) * 100) : 0;
        @endphp

        <div class="p-4 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/80 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <!-- <span class="text-slate-500 dark:text-slate-400 text-[11px] block">Status Kelengkapan Pengukuran Periode Ini:</span> -->
                    <span class="font-black text-slate-900 dark:text-white text-sm">
                        <span id="stat_sudah" class="text-emerald-600 dark:text-emerald-400">{{ $sudahDiukur }}</span> dari <span id="stat_total" data-total="{{ $totalBalita }}">{{ $totalBalita }}</span> Balita Sudah Diukur (<span id="stat_percent">{{ $percent }}</span>%)
                    </span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full sm:w-48 bg-slate-200 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden border border-slate-300 dark:border-slate-700">
                <div id="progress_bar" class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
            </div>
        </div>
    </div>

    <!-- Alert Success Notification (AJAX or Session) -->
    <div id="ajax_alert" class="hidden p-4 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center justify-between gap-2 shadow-lg animate-fade-in">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <span id="ajax_alert_text">Pengukuran berhasil disimpan!</span>
        </div>
        <button onclick="document.getElementById('ajax_alert').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 dark:hover:text-white p-1">✕</button>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Main Card Container & Filter Controls -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl dark:shadow-black/40 space-y-5 transition-colors duration-200">

        <!-- Controls Bar: Title, Search, and Status Toggle -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800/80 pb-4">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Daftar Balita Posyandu</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tekan nama/kartu balita untuk melihat detail pengukuran</p>
            </div>

            <!-- Client-Side Controls (Search & Status Filter) -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <!-- Search Input (AJAX min 3 chars with debounce & spinner) -->
                <div class="relative min-w-[240px]">
                    <input type="text" id="search_nama" oninput="handleSearchInput()" placeholder="Cari Nama Balita... (Min. 3 Karakter)"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-semibold text-xs rounded-2xl pl-9 pr-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none shadow-inner transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500 min-h-[42px]">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                        <svg id="search_icon" class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <svg id="search_spinner" class="hidden w-4 h-4 animate-spin text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Toggle Chips (Semua / Belum Diukur) -->
                <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-950 p-1 rounded-2xl border border-slate-200 dark:border-slate-800 shrink-0">
                    <button type="button" id="filter_btn_all" onclick="setStatusFilter('all')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all min-h-[36px] bg-white dark:bg-slate-800 text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 shadow-sm">
                        Semua ({{ $totalBalita }})
                    </button>
                    <button type="button" id="filter_btn_belum" onclick="setStatusFilter('belum')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all min-h-[36px] text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200">
                        Belum Diukur ({{ $totalBalita - $sudahDiukur }})
                    </button>
                </div>
            </div>
        </div>

        @if($anaks->count() > 0)
            <!-- Mobile & Tablet Grid (Mobile-First) -->
            <div id="balita_grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($anaks as $anak)
                    @php
                        $p = $anak->pengukuran_periode;
                        $sudah = !is_null($p);
                        // Calculate age for selected period
                        $tglUkurDummy = sprintf('%04d-%02d-01', $selectedTahun, $selectedBulan);
                        $usiaPeriode = max(0, (int) floor(\Carbon\Carbon::parse($anak->tanggal_lahir)->diffInMonths(\Carbon\Carbon::parse($tglUkurDummy))));
                    @endphp

                    <div id="card_anak_{{ $anak->id }}"
                        data-nama="{{ strtolower($anak->nama) }}"
                        data-sudah="{{ $sudah ? '1' : '0' }}"
                        class="card-balita bg-slate-50/90 dark:bg-slate-950/90 border {{ $sudah ? 'border-emerald-500/30' : 'border-slate-200 dark:border-slate-800' }} hover:border-slate-300 dark:hover:border-slate-700 p-4 rounded-2xl space-y-3 transition-all shadow-sm relative group">

                        <!-- Child Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <a href="{{ route('balita.show', $anak->id) }}" class="font-black text-slate-900 dark:text-white text-sm hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors block">
                                    {{ $anak->nama }}
                                </a>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold block mt-0.5">
                                    {{ $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} • {{ $usiaPeriode }} Bulan
                                </span>
                            </div>

                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-400 border border-slate-300 dark:border-slate-700 shrink-0">
                                {{ $anak->posyandu->nama ?? 'Posyandu' }}
                            </span>
                        </div>

                        <!-- Status Indicator & Action Button -->
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between gap-2">
                            <div id="status_container_{{ $anak->id }}">
                                @if($sudah)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30 shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span id="badge_text_{{ $anak->id }}">Sudah: {{ number_format($p->tinggi_cm, 1) }} cm / {{ number_format($p->berat_kg, 1) }} kg</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-700">
                                        <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500 animate-pulse"></span>
                                        <span id="badge_text_{{ $anak->id }}">Belum Diukur</span>
                                    </span>
                                @endif
                            </div>

                            <button type="button"
                                onclick="openInputModal({{ $anak->id }}, '{{ addslashes($anak->nama) }}', '{{ $sudah ? $p->tinggi_cm : '' }}', '{{ $sudah ? $p->berat_kg : '' }}', {{ $usiaPeriode }})"
                                class="py-1.5 px-3.5 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-black rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-md active:scale-95 min-h-[38px]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $sudah ? 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' : 'M12 4v16m8-8H4' }}"/>
                                </svg>
                                <span>{{ $sudah ? 'Edit Data' : 'Input Data' }}</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Empty Search / Filter Notice -->
            <div id="no_results_notice" class="hidden text-center py-12 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 rounded-2xl text-slate-500 dark:text-slate-400 text-xs space-y-2">
                <svg class="w-8 h-8 mx-auto text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="font-bold text-slate-900 dark:text-white text-sm">Tidak ada balita yang ditemukan</p>
                <p class="text-[11px] text-slate-500">Coba ubah kata kunci pencarian atau matikan filter "Belum Diukur".</p>
            </div>
        @else
            <div class="text-center py-12 text-slate-500 dark:text-slate-400 text-xs">
                Belum ada data balita aktif di Posyandu ini.
            </div>
        @endif
    </div>

</div>

<!-- POPUP MODAL INPUT PENGUKURAN (MOBILE-FIRST) -->
<div id="inputModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 w-full max-w-md rounded-3xl p-5 sm:p-6 shadow-2xl space-y-5 animate-scale-in relative transition-colors duration-200">

        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Modal Input Fast UI</span>
                <h3 id="modal_anak_nama" class="text-lg font-black text-slate-900 dark:text-white">Nama Balita</h3>
                <p id="modal_sub" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Periode: {{ \Carbon\Carbon::createFromDate($selectedTahun, $selectedBulan, 1)->translatedFormat('F Y') }}</p>
            </div>
            <button type="button" onclick="closeInputModal()" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center border border-slate-200 dark:border-slate-700 transition-colors">
                ✕
            </button>
        </div>

        <!-- Form Modal -->
        <form id="modalForm" onsubmit="submitModalForm(event)" class="space-y-4">
            @csrf
            <input type="hidden" name="anak_id" id="modal_anak_id">
            <input type="hidden" name="bulan_ukur" value="{{ $selectedBulan }}">
            <input type="hidden" name="tahun_ukur" value="{{ $selectedTahun }}">

            <div class="space-y-4">
                <!-- Tinggi Badan (cm) -->
                <div>
                    <label for="modal_tinggi_cm" class="block text-xs font-extrabold text-slate-700 dark:text-slate-200 mb-1">
                        Tinggi Badan (cm) <span class="text-rose-600 dark:text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" step="0.1" min="30" max="150" name="tinggi_cm" id="modal_tinggi_cm" required
                            placeholder="Contoh: 85.5"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-extrabold text-base rounded-2xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none shadow-inner">
                        <span class="absolute right-4 top-3.5 text-xs font-bold text-slate-400 dark:text-slate-500">cm</span>
                    </div>
                </div>

                <!-- Berat Badan (kg) -->
                <div>
                    <label for="modal_berat_kg" class="block text-xs font-extrabold text-slate-700 dark:text-slate-200 mb-1">
                        Berat Badan (kg) <span class="text-rose-600 dark:text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" step="0.1" min="1" max="40" name="berat_kg" id="modal_berat_kg" required
                            placeholder="Contoh: 11.8"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-teal-600 dark:text-teal-400 font-extrabold text-base rounded-2xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none shadow-inner">
                        <span class="absolute right-4 top-3.5 text-xs font-bold text-slate-400 dark:text-slate-500">kg</span>
                    </div>
                </div>
            </div>

            <!-- Error message container -->
            <div id="modal_error" class="hidden p-3 rounded-xl bg-rose-500/10 dark:bg-rose-500/20 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-bold"></div>

            <!-- Submit Button -->
            <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                <button type="button" onclick="closeInputModal()" class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs min-h-[42px] transition-colors">
                    Batal
                </button>
                <button type="submit" id="btn_submit_modal" class="py-2.5 px-5 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-extrabold rounded-xl text-xs flex items-center justify-center gap-2 min-h-[42px] shadow-lg transition-all active:scale-95">
                    <svg id="spinner" class="hidden w-4 h-4 animate-spin text-slate-950" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Simpan Pengukuran</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentStatusFilter = 'all'; // 'all' or 'belum'
let searchDebounceTimer = null;
let initialGridHtml = '';
let isAjaxSearchActive = false;

document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('balita_grid');
    if (grid) {
        initialGridHtml = grid.innerHTML;
    }
});

function submitPeriode(selectEl) {
    const val = selectEl.value;
    const parts = val.split('-');
    if (parts.length === 2) {
        document.getElementById('param_bulan').value = parts[0];
        document.getElementById('param_tahun').value = parts[1];
        selectEl.form.submit();
    }
}

function setStatusFilter(filterType) {
    currentStatusFilter = filterType;

    const btnAll = document.getElementById('filter_btn_all');
    const btnBelum = document.getElementById('filter_btn_belum');

    if (filterType === 'all') {
        btnAll.className = 'px-3.5 py-1.5 rounded-xl text-xs font-black transition-all min-h-[36px] bg-white dark:bg-slate-800 text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 shadow-sm';
        btnBelum.className = 'px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all min-h-[36px] text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200';
    } else {
        btnBelum.className = 'px-3.5 py-1.5 rounded-xl text-xs font-black transition-all min-h-[36px] bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30 shadow-sm';
        btnAll.className = 'px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all min-h-[36px] text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200';
    }

    filterBalitaList();
}

function handleSearchInput() {
    const searchVal = document.getElementById('search_nama').value.trim();
    const searchIcon = document.getElementById('search_icon');
    const searchSpinner = document.getElementById('search_spinner');

    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);

    if (searchVal.length < 3) {
        if (searchIcon) searchIcon.classList.remove('hidden');
        if (searchSpinner) searchSpinner.classList.add('hidden');

        if (isAjaxSearchActive) {
            isAjaxSearchActive = false;
            const grid = document.getElementById('balita_grid');
            if (grid && initialGridHtml) {
                grid.innerHTML = initialGridHtml;
            }
        }
        filterBalitaList();
        return;
    }

    if (searchIcon) searchIcon.classList.add('hidden');
    if (searchSpinner) searchSpinner.classList.remove('hidden');

    searchDebounceTimer = setTimeout(() => {
        performBalitaSearch(searchVal);
    }, 300);
}

async function performBalitaSearch(query) {
    const bulan = document.getElementById('param_bulan').value;
    const tahun = document.getElementById('param_tahun').value;
    const searchIcon = document.getElementById('search_icon');
    const searchSpinner = document.getElementById('search_spinner');

    try {
        const url = `{{ route('balita.search') }}?q=${encodeURIComponent(query)}&bulan=${bulan}&tahun=${tahun}`;
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const json = await response.json();

        if (json.success) {
            isAjaxSearchActive = true;
            renderSearchGridResults(json.data);
        }
    } catch (err) {
        console.error('Error fetching balita search:', err);
    } finally {
        if (searchIcon) searchIcon.classList.remove('hidden');
        if (searchSpinner) searchSpinner.classList.add('hidden');
    }
}

function renderSearchGridResults(items) {
    const grid = document.getElementById('balita_grid');
    if (!grid) return;

    if (items.length === 0) {
        grid.innerHTML = '';
        const notice = document.getElementById('no_results_notice');
        if (notice) notice.classList.remove('hidden');
        return;
    }

    let html = '';
    items.forEach(anak => {
        const sudah = anak.sudah_diukur;
        const p = anak.pengukuran;
        const namaEscaped = anak.nama.replace(/'/g, "\\'");

        html += `
            <div id="card_anak_${anak.id}"
                data-nama="${anak.nama.toLowerCase()}"
                data-sudah="${sudah ? '1' : '0'}"
                class="card-balita bg-slate-50/90 dark:bg-slate-950/90 border ${sudah ? 'border-emerald-500/30' : 'border-slate-200 dark:border-slate-800'} hover:border-slate-300 dark:hover:border-slate-700 p-4 rounded-2xl space-y-3 transition-all shadow-sm relative group">

                <div class="flex items-start justify-between gap-2">
                    <div>
                        <a href="${anak.show_url}" class="font-black text-slate-900 dark:text-white text-sm hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors block">
                            ${anak.nama}
                        </a>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold block mt-0.5">
                            ${anak.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'} • ${anak.usia_periode} Bulan
                        </span>
                    </div>

                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-400 border border-slate-300 dark:border-slate-700 shrink-0">
                        ${anak.posyandu_nama}
                    </span>
                </div>

                <div class="pt-2 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between gap-2">
                    <div id="status_container_${anak.id}">
                        ${sudah ? `
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30 shadow-sm">
                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span id="badge_text_${anak.id}">Sudah: ${p ? p.tinggi_formatted : ''} cm / ${p ? p.berat_formatted : ''} kg</span>
                            </span>
                        ` : `
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-700">
                                <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500 animate-pulse"></span>
                                <span id="badge_text_${anak.id}">Belum Diukur</span>
                            </span>
                        `}
                    </div>

                    <button type="button"
                        onclick="openInputModal(${anak.id}, '${namaEscaped}', '${p ? p.tinggi_cm : ''}', '${p ? p.berat_kg : ''}', ${anak.usia_periode})"
                        class="py-1.5 px-3.5 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-black rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-md active:scale-95 min-h-[38px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="${sudah ? 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' : 'M12 4v16m8-8H4'}"/>
                        </svg>
                        <span>${sudah ? 'Edit Data' : 'Input Data'}</span>
                    </button>
                </div>
            </div>
        `;
    });

    grid.innerHTML = html;
    filterBalitaList();
}

function filterBalitaList() {
    const cards = document.querySelectorAll('.card-balita');
    let visibleCount = 0;

    cards.forEach(card => {
        const sudah = card.getAttribute('data-sudah') === '1';
        const matchStatus = currentStatusFilter === 'all' || (currentStatusFilter === 'belum' && !sudah);

        if (matchStatus) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    const notice = document.getElementById('no_results_notice');
    if (notice) {
        if (visibleCount === 0) {
            notice.classList.remove('hidden');
        } else {
            notice.classList.add('hidden');
        }
    }
}

function openInputModal(id, nama, tinggi, berat, usiaBulan) {
    document.getElementById('modal_anak_id').value = id;
    document.getElementById('modal_anak_nama').innerText = nama;
    document.getElementById('modal_tinggi_cm').value = tinggi || '';
    document.getElementById('modal_berat_kg').value = berat || '';
    document.getElementById('modal_error').classList.add('hidden');

    document.getElementById('inputModal').classList.remove('hidden');
    document.getElementById('modal_tinggi_cm').focus();
}

function closeInputModal() {
    document.getElementById('inputModal').classList.add('hidden');
}

async function submitModalForm(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btn_submit_modal');
    const spinner = document.getElementById('spinner');
    const errBox = document.getElementById('modal_error');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    errBox.classList.add('hidden');

    const formData = new FormData(form);

    try {
        const response = await fetch("{{ route('pengukuran.store') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: formData
        });

        const data = await response.json();

        if (response.ok && data.success) {
            closeInputModal();

            // Update Card UI
            const anakId = data.pengukuran.anak_id;
            const container = document.getElementById('status_container_' + anakId);
            const card = document.getElementById('card_anak_' + anakId);

            if (container) {
                container.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30 shadow-sm">
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Sudah: ${data.pengukuran.tinggi_cm} cm / ${data.pengukuran.berat_kg} kg</span>
                    </span>
                `;
            }

            if (card) {
                const wasSudah = card.getAttribute('data-sudah') === '1';

                card.setAttribute('data-sudah', '1');
                card.classList.remove('border-slate-200', 'dark:border-slate-800');
                card.classList.add('border-emerald-500/30');

                // Jika sebelumnya belum diukur, tambahkan statistik real-time di atas
                if (!wasSudah) {
                    const statSudahEl = document.getElementById('stat_sudah');
                    const statTotalEl = document.getElementById('stat_total');
                    const statPercentEl = document.getElementById('stat_percent');
                    const progressBarEl = document.getElementById('progress_bar');

                    if (statSudahEl && statTotalEl) {
                        let currentSudah = parseInt(statSudahEl.innerText) || 0;
                        let totalBalita = parseInt(statTotalEl.getAttribute('data-total')) || parseInt(statTotalEl.innerText) || 1;

                        currentSudah++;
                        let newPercent = Math.round((currentSudah / totalBalita) * 100);

                        statSudahEl.innerText = currentSudah;
                        if (statPercentEl) statPercentEl.innerText = newPercent;
                        if (progressBarEl) progressBarEl.style.width = newPercent + '%';
                    }
                }
            }

            // Show Toast Alert
            const alertBox = document.getElementById('ajax_alert');
            document.getElementById('ajax_alert_text').innerText = data.message;
            alertBox.classList.remove('hidden');

            // Re-apply client-side filter so if "Belum Diukur" was active, card updates state
            filterBalitaList();
        } else {
            errBox.innerText = data.message || "Terjadi kesalahan saat menyimpan data.";
            errBox.classList.remove('hidden');
        }
    } catch (err) {
        errBox.innerText = "Gagal terhubung ke server. Silakan coba lagi.";
        errBox.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}

// Realtime Background Sync Across Devices (Polling 5 Detik)
setInterval(async () => {
    // Jangan poll jika modal sedang terbuka agar tidak mengganggu ketikan user
    const modal = document.getElementById('inputModal');
    if (modal && !modal.classList.contains('hidden')) return;

    try {
        const response = await fetch(window.location.href, {
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            }
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success && Array.isArray(data.anaks)) {
                data.anaks.forEach(item => {
                    const card = document.getElementById('card_anak_' + item.id);
                    const container = document.getElementById('status_container_' + item.id);
                    if (!card || !container) return;

                    if (item.sudah) {
                        const tStr = item.tinggi_cm ? `${item.tinggi_cm} cm` : '-';
                        const bStr = item.berat_kg ? `${item.berat_kg} kg` : '-';
                        container.innerHTML = `
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30 shadow-sm">
                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Sudah: ${tStr} / ${bStr}</span>
                            </span>
                        `;
                        card.setAttribute('data-sudah', '1');
                        card.classList.remove('border-slate-200', 'dark:border-slate-800');
                        card.classList.add('border-emerald-500/30');
                    }
                });

                // Update Statistik Header
                const statSudahEl = document.getElementById('stat_sudah');
                const statTotalEl = document.getElementById('stat_total');
                const statPercentEl = document.getElementById('stat_percent');
                const progressBarEl = document.getElementById('progress_bar');

                if (statSudahEl && statTotalEl) {
                    const total = data.totalBalita || 1;
                    const sudah = data.sudahDiukur || 0;
                    const percent = Math.round((sudah / total) * 100);

                    statSudahEl.innerText = sudah;
                    if (statPercentEl) statPercentEl.innerText = percent;
                    if (progressBarEl) progressBarEl.style.width = percent + '%';
                }

                filterBalitaList();
            }
        }
    } catch (e) {
        // Silent catch for background polling
    }
}, 5000);
</script>

@endsection
