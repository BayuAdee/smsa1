@extends('layouts.app')

@section('title', 'Input Pengukuran Bulanan')
@section('page-title', 'Form Input Pengukuran Bulanan Balita')

@section('content')

<div class="max-w-xl mx-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-8 shadow-xl dark:shadow-black/40 transition-colors duration-200">

    <div class="mb-6">
        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Catat Pengukuran Fisik Bulanan</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Masukkan hasil penimbangan berat badan dan pengukuran tinggi badan anak di Posyandu.</p>
    </div>

    <form action="{{ route('pengukuran.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Pilih Balita -->
        <div>
            <label for="anak_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Pilih Balita <span class="text-rose-600 dark:text-rose-400">*</span></label>
            <select name="anak_id" id="anak_id" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 text-slate-900 dark:text-white rounded-2xl p-3.5 text-sm min-h-[48px] outline-none transition-colors">
                <option value="">-- Pilih Balita --</option>
                @foreach($anaks as $a)
                    <option value="{{ $a->id }}" {{ old('anak_id', $selectedAnakId) == $a->id ? 'selected' : '' }}>
                        {{ $a->nama }} ({{ $a->posyandu->nama ?? '' }}) - Usia: {{ $a->usia_bulan }} Bulan
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Tanggal Pengukuran -->
        <div>
            <label for="tanggal_ukur" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Tanggal Pengukuran <span class="text-rose-600 dark:text-rose-400">*</span></label>
            <input type="date" id="tanggal_ukur" name="tanggal_ukur" value="{{ old('tanggal_ukur', now()->format('Y-m-d')) }}" required
                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 text-slate-900 dark:text-white rounded-2xl p-3.5 text-sm min-h-[48px] outline-none transition-colors">
        </div>

        <!-- Inputs: Tinggi (cm) & Berat (kg) with decimal keypads -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="tinggi_cm" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Tinggi / Panjang Badan (cm) <span class="text-rose-600 dark:text-rose-400">*</span></label>
                <div class="relative">
                    <input type="number" id="tinggi_cm" name="tinggi_cm" value="{{ old('tinggi_cm') }}" step="0.1" inputmode="decimal" min="30" max="150" required
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 text-emerald-600 dark:text-emerald-400 font-extrabold placeholder-slate-400 dark:placeholder-slate-500 rounded-2xl p-3.5 pr-12 text-sm min-h-[48px] outline-none transition-colors"
                           placeholder="Contoh: 75.5">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-xs font-bold">
                        cm
                    </div>
                </div>
            </div>

            <div>
                <label for="berat_kg" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Berat Badan (kg) <span class="text-rose-600 dark:text-rose-400">*</span></label>
                <div class="relative">
                    <input type="number" id="berat_kg" name="berat_kg" value="{{ old('berat_kg') }}" step="0.1" inputmode="decimal" min="1" max="40" required
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 text-teal-600 dark:text-teal-400 font-extrabold placeholder-slate-400 dark:placeholder-slate-500 rounded-2xl p-3.5 pr-12 text-sm min-h-[48px] outline-none transition-colors"
                           placeholder="Contoh: 8.4">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 text-xs font-bold">
                        kg
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-xs text-emerald-800 dark:text-emerald-300 flex items-start gap-2">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Setelah disimpan, sistem akan langsung menghitung Z-score WHO & memperbarui Ranking Prioritas Stunting SAW secara otomatis.</span>
        </div>

        <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
            <a href="{{ route('dashboard') }}" class="px-5 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-2xl text-xs min-h-[44px] flex items-center border border-slate-200 dark:border-slate-700 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 text-slate-950 font-extrabold rounded-2xl shadow-lg shadow-emerald-500/20 active:scale-95 transition-all text-xs min-h-[44px] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Simpan & Hitung SAW</span>
            </button>
        </div>
    </form>
</div>

@endsection
