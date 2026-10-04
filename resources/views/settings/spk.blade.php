@extends('layouts.app')

@section('title', 'Pengaturan Parameter SPK SAW')
@section('page-title', 'Pengaturan Parameter SPK SAW')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto pb-12">

    <!-- Header Section -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl dark:shadow-black/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors duration-200">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">Pengaturan Kriteria & Parameter SPK SAW</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola variabel C1–C4, persentase bobot, opsi sub-kriteria, dan ambang batas (threshold) kategori risiko stunting.</p>
                </div>
            </div>
        </div>

        <button type="button" onclick="openResetModal()" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-extrabold rounded-2xl text-xs flex items-center justify-center gap-2 min-h-[44px] transition-all border border-slate-300 dark:border-slate-700 shadow-sm active:scale-95 shrink-0">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>Reset ke Default Standar</span>
        </button>
    </div>

    <!-- Form Pengaturan -->
    <form action="{{ route('settings.spk.update') }}" method="POST" id="spk-settings-form" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- SECTION 1: KRITERIA C1 - C4 -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl dark:shadow-black/40 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>1. Konfigurasi Variabel & Bobot Kriteria (C1 – C4)</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tentukan variabel sumber data, label nama, persentase bobot, serta opsi penilaian sub-kriteria.</p>
                </div>

                <!-- Total Bobot Live Tracker Badge -->
                <div id="total-bobot-container" class="flex items-center gap-2.5 px-3.5 py-2 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 transition-all">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" id="total-bobot-dot"></div>
                    <span class="text-xs font-bold">Total Bobot:</span>
                    <span class="text-xs font-black tracking-wide" id="total-bobot-display">100%</span>
                </div>
            </div>

            <!-- Total Bobot Progress Bar -->
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                <div id="total-bobot-bar" class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-300" style="width: 100%;"></div>
            </div>

            <!-- Grid 4 Kriteria -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                @foreach($criterias as $index => $crit)
                    <div class="bg-slate-50/80 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 space-y-4 relative transition-all hover:border-emerald-500/40 focus-within:border-emerald-500 flex flex-col justify-between">
                        <input type="hidden" name="criterias[{{ $index }}][id]" value="{{ $crit->id }}">

                        <div class="space-y-4">
                            <!-- Card Header -->
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-xl bg-emerald-500 text-slate-950 font-black text-xs flex items-center justify-center shadow-md">
                                        {{ $crit->kode }}
                                    </span>
                                    <div>
                                        <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Slot Kriteria {{ $crit->kode }}</span>
                                        <span class="block text-[10px] text-slate-400">Urutan Prioritas #{{ $crit->urutan }}</span>
                                    </div>
                                </div>

                                <!-- Jenis Kriteria Dropdown (Cost / Benefit) -->
                                <div class="flex items-center gap-1.5">
                                    <label for="jenis_{{ $index }}" class="text-[10px] font-bold text-slate-500 uppercase">Sifat:</label>
                                    <select name="criterias[{{ $index }}][jenis]" id="jenis_{{ $index }}" class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl px-2.5 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                        <option value="cost" {{ $crit->jenis === 'cost' ? 'selected' : '' }}>Cost (Biaya / Risiko)</option>
                                        <option value="benefit" {{ $crit->jenis === 'benefit' ? 'selected' : '' }}>Benefit (Manfaat)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Input Label / Nama Kriteria -->
                            <div>
                                <label for="nama_{{ $index }}" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Nama Label Kriteria <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="criterias[{{ $index }}][nama]" id="nama_{{ $index }}" value="{{ old('criterias.'.$index.'.nama', $crit->nama) }}" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-colors" placeholder="Masukkan nama kriteria">
                            </div>

                            <!-- Input Sumber Variabel & Bobot Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- Pilihan Sumber Variabel / Perhitungan -->
                                <div class="sm:col-span-2">
                                    <label for="sumber_{{ $index }}" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Sumber Variabel Data <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="criterias[{{ $index }}][tipe_sumber]" id="sumber_{{ $index }}" onchange="updateSourceInfo({{ $index }})" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-medium rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                        @foreach($availableSources as $key => $src)
                                            <option value="{{ $key }}" {{ (old('criterias.'.$index.'.tipe_sumber', $crit->tipe_sumber) === $key) ? 'selected' : '' }}>
                                                {{ $src['label'] }} ({{ $src['type'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 block leading-tight" id="source-desc-{{ $index }}">
                                        {{ $availableSources[$crit->tipe_sumber]['desc'] ?? '' }}
                                    </span>
                                </div>

                                <!-- Input Bobot Persentase -->
                                <div>
                                    <label for="bobot_{{ $index }}" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Bobot (%) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" step="0.01" min="0" max="100" name="criterias[{{ $index }}][bobot]" id="bobot_{{ $index }}" value="{{ old('criterias.'.$index.'.bobot', $crit->bobot_persen) }}" required oninput="calculateTotalWeight()" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl pl-3.5 pr-8 py-2.5 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-colors weight-input">
                                        <span class="absolute right-3 top-2.5 text-xs font-extrabold text-slate-400">%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Sub-Kriteria Builder (Muncul saat Sumber Data = Kustom) -->
                            <div id="sub-kriteria-box-{{ $index }}" class="{{ old('criterias.'.$index.'.tipe_sumber', $crit->tipe_sumber) === 'kustom' ? '' : 'hidden' }} space-y-3 pt-3.5 border-t border-slate-200/80 dark:border-slate-800/80">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span>Opsi Jawaban & Skor Risiko (Untuk Kader)</span>
                                        </h4>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Pilihan opsi yang akan dipilih kader saat pengukuran balita.</p>
                                    </div>

                                    <!-- Preset Quick Buttons -->
                                    <div class="flex items-center gap-1 flex-wrap">
                                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Preset:</span>
                                        <button type="button" onclick="applyPreset({{ $index }}, 'asi')" class="px-2 py-1 text-[10px] font-bold bg-slate-200 dark:bg-slate-800 hover:bg-emerald-500 hover:text-slate-950 dark:hover:bg-emerald-500 dark:hover:text-slate-950 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                                            ASI Eksklusif
                                        </button>
                                        <button type="button" onclick="applyPreset({{ $index }}, 'yant')" class="px-2 py-1 text-[10px] font-bold bg-slate-200 dark:bg-slate-800 hover:bg-emerald-500 hover:text-slate-950 dark:hover:bg-emerald-500 dark:hover:text-slate-950 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                                            Ya / Tidak
                                        </button>
                                        <button type="button" onclick="applyPreset({{ $index }}, 'tingkat')" class="px-2 py-1 text-[10px] font-bold bg-slate-200 dark:bg-slate-800 hover:bg-emerald-500 hover:text-slate-950 dark:hover:bg-emerald-500 dark:hover:text-slate-950 text-slate-700 dark:text-slate-300 rounded-lg transition-colors">
                                            3 Tingkat
                                        </button>
                                    </div>
                                </div>

                                <!-- List Opsi Sub-Kriteria -->
                                <div id="sub-kriteria-list-{{ $index }}" class="space-y-2">
                                    @php
                                        $existingSubs = old('criterias.'.$index.'.sub_kriteria', $crit->sub_kriteria ?? []);
                                        if (empty($existingSubs)) {
                                            $existingSubs = [
                                                ['label' => 'Kategori Aman / Ideal', 'skor' => 1.0],
                                                ['label' => 'Kategori Sedang / Waspada', 'skor' => 2.0],
                                                ['label' => 'Kategori Berisiko Tinggi', 'skor' => 4.0],
                                            ];
                                        }
                                    @endphp
                                    @foreach($existingSubs as $subIdx => $subItem)
                                        <div class="sub-item flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-2 shadow-sm">
                                            <div class="flex-1">
                                                <input type="text" name="criterias[{{ $index }}][sub_kriteria][{{ $subIdx }}][label]" value="{{ $subItem['label'] ?? '' }}" placeholder="Nama opsi / kategori" class="w-full text-xs bg-transparent border-0 text-slate-900 dark:text-white font-medium focus:ring-0 focus:outline-none placeholder-slate-400">
                                            </div>
                                            <div class="w-40 shrink-0 flex items-center gap-1.5 border-l border-slate-200 dark:border-slate-800 pl-2">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase shrink-0">Skor:</label>
                                                <select name="criterias[{{ $index }}][sub_kriteria][{{ $subIdx }}][skor]" class="w-full bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-900 dark:text-white rounded-lg px-2 py-1 border-0 focus:ring-1 focus:ring-emerald-500">
                                                    <option value="1.0" {{ ((float)($subItem['skor'] ?? 1.0) == 1.0) ? 'selected' : '' }}>1.0 (Ideal / Aman)</option>
                                                    <option value="2.0" {{ ((float)($subItem['skor'] ?? 1.0) == 2.0) ? 'selected' : '' }}>2.0 (Risiko Sedang)</option>
                                                    <option value="3.0" {{ ((float)($subItem['skor'] ?? 1.0) == 3.0) ? 'selected' : '' }}>3.0 (Cukup Berisiko)</option>
                                                    <option value="4.0" {{ ((float)($subItem['skor'] ?? 1.0) == 4.0) ? 'selected' : '' }}>4.0 (Risiko Tinggi)</option>
                                                </select>
                                            </div>
                                            <button type="button" onclick="removeSubOption(this)" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-500/10 transition-colors shrink-0" title="Hapus Opsi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" onclick="addSubOption({{ $index }})" class="w-full py-2 px-3 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-dashed border-emerald-500/30 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Tambah Opsi Jawaban Baru</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- SECTION 2: AMBANG BATAS (THRESHOLD) KATEGORI RISIKO -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl dark:shadow-black/40 space-y-6">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>2. Ambang Batas (Threshold) Kategori Risiko (Nilai Preferensi V)</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Pada metode SAW Stunting, semakin kecil Nilai Preferensi $V$ menunjukkan tingkat risiko stunting yang semakin tinggi. Atur batas desimal untuk mengelompokkan balita ke kategori Risiko Tinggi, Sedang, atau Rendah.
                </p>
            </div>

            <!-- Visual Range Representation Bar -->
            <div class="space-y-2">
                <div class="flex justify-between items-center text-[11px] font-bold text-slate-500">
                    <span>0.0000 (Risiko Tertinggi)</span>
                    <span>1.0000 (Risiko Terendah)</span>
                </div>
                <div class="w-full h-8 rounded-2xl flex overflow-hidden p-1 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 shadow-inner font-black text-[10px] text-white">
                    <div id="bar-tinggi" class="bg-gradient-to-r from-rose-500 to-rose-600 rounded-l-xl flex items-center justify-center transition-all duration-300 shadow-sm" style="width: 63.74%;">
                        <span class="truncate px-2">Risiko Tinggi (<span id="label-val-tinggi">{{ number_format($thresholdTinggi, 4) }}</span>)</span>
                    </div>
                    <div id="bar-sedang" class="bg-gradient-to-r from-amber-500 to-amber-600 flex items-center justify-center transition-all duration-300 shadow-sm" style="width: 14.83%;">
                        <span class="truncate px-2">Risiko Sedang</span>
                    </div>
                    <div id="bar-rendah" class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-r-xl flex-1 flex items-center justify-center transition-all duration-300 shadow-sm">
                        <span class="truncate px-2">Risiko Rendah (&ge; <span id="label-val-sedang">{{ number_format($thresholdSedang, 4) }}</span>)</span>
                    </div>
                </div>
            </div>

            <!-- Threshold Inputs Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <!-- Threshold Tinggi -->
                <div class="bg-slate-50/80 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500 shrink-0"></span>
                        <label for="threshold_tinggi" class="text-xs font-bold text-slate-800 dark:text-slate-200">
                            Batas Ambang Atas Risiko Tinggi (Prioritas 1) <span class="text-rose-500">*</span>
                        </label>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Balita diklasifikasikan <strong class="text-rose-600 dark:text-rose-400">Risiko Tinggi</strong> jika Nilai $V <$ nilai ini.
                    </p>
                    <input type="number" step="0.0001" min="0.0001" max="0.9999" name="threshold_tinggi" id="threshold_tinggi" value="{{ old('threshold_tinggi', number_format($thresholdTinggi, 4, '.', '')) }}" required oninput="updateThresholdBars()" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-colors">
                </div>

                <!-- Threshold Sedang -->
                <div class="bg-slate-50/80 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500 shrink-0"></span>
                        <label for="threshold_sedang" class="text-xs font-bold text-slate-800 dark:text-slate-200">
                            Batas Ambang Atas Risiko Sedang (Prioritas 2) <span class="text-rose-500">*</span>
                        </label>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Balita diklasifikasikan <strong class="text-amber-600 dark:text-amber-400">Risiko Sedang</strong> jika $V$ di antara Batas Tinggi dan Batas Sedang.
                    </p>
                    <input type="number" step="0.0001" min="0.0001" max="0.9999" name="threshold_sedang" id="threshold_sedang" value="{{ old('threshold_sedang', number_format($thresholdSedang, 4, '.', '')) }}" required oninput="updateThresholdBars()" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-colors">
                </div>
            </div>
        </div>

        <!-- SECTION 3: AKSI & SIMPAN -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl dark:shadow-black/40 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <input type="checkbox" name="recalculate_now" id="recalculate_now" value="1" checked class="w-4 h-4 text-emerald-600 bg-slate-100 border-slate-300 rounded focus:ring-emerald-500 dark:focus:ring-emerald-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                <label for="recalculate_now" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                    Hitung ulang (recalculate) data ranking SPK SAW periode berjalan ({{ now()->translatedFormat('F Y') }}) secara otomatis setelah disimpan.
                </label>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="submit" id="save-button" class="w-full sm:w-auto py-3 px-6 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-black rounded-2xl text-xs flex items-center justify-center gap-2 min-h-[44px] shadow-lg shadow-emerald-500/20 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Perubahan Parameter</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Modal Konfirmasi Reset ke Default -->
<div id="reset-modal" class="fixed inset-0 z-50 hidden bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-500 flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <div class="text-center space-y-1">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Kembalikan ke Default Standar?</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Seluruh kriteria C1–C4 akan dikembalikan ke rumus baku stunting medis:
                <br><strong>C1 (TB/U: 45%), C2 (KMS: 25%), C3 (BB/U: 20%), C4 (BBLR: 10%)</strong>
                serta threshold standar (0.6374 & 0.7857).
            </p>
        </div>

        <form action="{{ route('settings.spk.reset') }}" method="POST" class="pt-2">
            @csrf
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeResetModal()" class="flex-1 py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-xl text-xs transition-colors shadow-lg shadow-amber-500/20">
                    Ya, Reset Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const sourceDescriptions = @json(collect($availableSources)->mapWithKeys(fn($s, $k) => [$k => $s['desc']]));

const presetData = {
    asi: [
        { label: 'ASI Eksklusif 6 Bulan Penuh', skor: '1.0' },
        { label: 'Campur Susu Formula', skor: '2.0' },
        { label: 'Tidak Pernah Diberi ASI', skor: '4.0' }
    ],
    yant: [
        { label: 'Ya / Memenuhi Syarat', skor: '1.0' },
        { label: 'Tidak / Belum Memenuhi Syarat', skor: '4.0' }
    ],
    tingkat: [
        { label: 'Tingkat Baik / Ideal / Aman', skor: '1.0' },
        { label: 'Tingkat Sedang / Cukup / Waspada', skor: '2.0' },
        { label: 'Tingkat Buruk / Kurang / Berisiko', skor: '4.0' }
    ]
};

function updateSourceInfo(index) {
    const select = document.getElementById(`sumber_${index}`);
    const descEl = document.getElementById(`source-desc-${index}`);
    const subBox = document.getElementById(`sub-kriteria-box-${index}`);

    if (select && descEl) {
        descEl.textContent = sourceDescriptions[select.value] || '';
    }

    if (select && subBox) {
        if (select.value === 'kustom') {
            subBox.classList.remove('hidden');
            const list = document.getElementById(`sub-kriteria-list-${index}`);
            if (list && list.children.length === 0) {
                applyPreset(index, 'tingkat');
            }
        } else {
            subBox.classList.add('hidden');
        }
    }
}

function addSubOption(critIndex, label = '', skor = '1.0') {
    const list = document.getElementById(`sub-kriteria-list-${critIndex}`);
    if (!list) return;

    const subIdx = Date.now() + Math.floor(Math.random() * 1000);
    const item = document.createElement('div');
    item.className = 'sub-item flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-2 shadow-sm animate-fade-in';
    item.innerHTML = `
        <div class="flex-1">
            <input type="text" name="criterias[${critIndex}][sub_kriteria][${subIdx}][label]" value="${label}" placeholder="Nama opsi / kategori" class="w-full text-xs bg-transparent border-0 text-slate-900 dark:text-white font-medium focus:ring-0 focus:outline-none placeholder-slate-400">
        </div>
        <div class="w-40 shrink-0 flex items-center gap-1.5 border-l border-slate-200 dark:border-slate-800 pl-2">
            <label class="text-[10px] font-bold text-slate-400 uppercase shrink-0">Skor:</label>
            <select name="criterias[${critIndex}][sub_kriteria][${subIdx}][skor]" class="w-full bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-900 dark:text-white rounded-lg px-2 py-1 border-0 focus:ring-1 focus:ring-emerald-500">
                <option value="1.0" ${skor == '1.0' ? 'selected' : ''}>1.0 (Ideal / Aman)</option>
                <option value="2.0" ${skor == '2.0' ? 'selected' : ''}>2.0 (Risiko Sedang)</option>
                <option value="3.0" ${skor == '3.0' ? 'selected' : ''}>3.0 (Cukup Berisiko)</option>
                <option value="4.0" ${skor == '4.0' ? 'selected' : ''}>4.0 (Risiko Tinggi)</option>
            </select>
        </div>
        <button type="button" onclick="removeSubOption(this)" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-500/10 transition-colors shrink-0" title="Hapus Opsi">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    `;
    list.appendChild(item);
}

function removeSubOption(btn) {
    const item = btn.closest('.sub-item');
    if (item) {
        item.remove();
    }
}

function applyPreset(critIndex, type) {
    const list = document.getElementById(`sub-kriteria-list-${critIndex}`);
    if (!list) return;

    list.innerHTML = '';
    const preset = presetData[type] || [];
    preset.forEach(opt => {
        addSubOption(critIndex, opt.label, opt.skor);
    });
}

function calculateTotalWeight() {
    const inputs = document.querySelectorAll('.weight-input');
    let total = 0;
    inputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    total = Math.round(total * 100) / 100;

    const display = document.getElementById('total-bobot-display');
    const container = document.getElementById('total-bobot-container');
    const dot = document.getElementById('total-bobot-dot');
    const bar = document.getElementById('total-bobot-bar');
    const saveBtn = document.getElementById('save-button');

    display.textContent = `${total}%`;
    bar.style.width = `${Math.min(100, Math.max(0, total))}%`;

    if (Math.abs(total - 100) < 0.01) {
        container.className = 'flex items-center gap-2.5 px-3.5 py-2 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 transition-all';
        dot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse';
        bar.className = 'h-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-300';
        saveBtn.disabled = false;
        saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        container.className = 'flex items-center gap-2.5 px-3.5 py-2 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 transition-all';
        dot.className = 'w-2.5 h-2.5 rounded-full bg-rose-500';
        bar.className = 'h-full bg-rose-500 transition-all duration-300';
    }
}

function updateThresholdBars() {
    const tinggiInput = parseFloat(document.getElementById('threshold_tinggi').value) || 0;
    const sedangInput = parseFloat(document.getElementById('threshold_sedang').value) || 0;

    const barTinggi = document.getElementById('bar-tinggi');
    const barSedang = document.getElementById('bar-sedang');
    const labelTinggi = document.getElementById('label-val-tinggi');
    const labelSedang = document.getElementById('label-val-sedang');

    if (tinggiInput < sedangInput && tinggiInput > 0 && sedangInput <= 1) {
        const tinggiWidth = Math.min(100, tinggiInput * 100);
        const sedangWidth = Math.max(0, Math.min(100 - tinggiWidth, (sedangInput - tinggiInput) * 100));

        barTinggi.style.width = `${tinggiWidth}%`;
        barSedang.style.width = `${sedangWidth}%`;

        labelTinggi.textContent = tinggiInput.toFixed(4);
        labelSedang.textContent = sedangInput.toFixed(4);
    }
}

function openResetModal() {
    document.getElementById('reset-modal').classList.remove('hidden');
}

function closeResetModal() {
    document.getElementById('reset-modal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    calculateTotalWeight();
    updateThresholdBars();
});
</script>
@endsection
