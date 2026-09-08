@extends('layouts.app')

@section('title', 'Tambah Balita Baru')
@section('page-title', 'Form Registrasi Balita Baru')

@section('content')

<div class="max-w-2xl mx-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-8 shadow-xl dark:shadow-black/40 transition-colors duration-200">

    <div class="mb-6">
        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Registrasi Balita Baru</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lengkapi data identitas fisik awal dan riwayat kelahiran balita.</p>
    </div>

    <form action="{{ route('balita.store') }}" method="POST" class="space-y-6">
        @csrf

        @if(Auth::user()->isBidan())
            <div>
                <label for="posyandu_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Posyandu Wilayah</label>
                <select name="posyandu_id" id="posyandu_id" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 dark:focus:border-emerald-400 text-slate-900 dark:text-white rounded-2xl p-3.5 text-sm min-h-[48px] transition-all">
                    @foreach($posyandus as $pos)
                        <option value="{{ $pos->id }}">{{ $pos->nama }} ({{ $pos->wilayah }})</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label for="nama" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Nama Lengkap Balita <span class="text-rose-500 dark:text-rose-400">*</span></label>
            <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required
                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 dark:focus:border-emerald-400 focus:ring-1 focus:ring-emerald-500 dark:focus:ring-emerald-400 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-2xl p-3.5 text-sm min-h-[48px] transition-all"
                   placeholder="Contoh: Muhammad Raihan">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="nik" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">NIK (Opsional)</label>
                <input type="text" id="nik" name="nik" value="{{ old('nik') }}" inputmode="numeric"
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 dark:focus:border-emerald-400 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-2xl p-3.5 text-sm min-h-[48px] transition-all"
                       placeholder="320101...">
            </div>

            <div>
                <label for="tanggal_lahir" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Tanggal Lahir <span class="text-rose-500 dark:text-rose-400">*</span></label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 dark:focus:border-emerald-400 text-slate-900 dark:text-white rounded-2xl p-3.5 text-sm min-h-[48px] transition-all">
            </div>
        </div>

        <!-- Radio Toggle: Jenis Kelamin -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Jenis Kelamin <span class="text-rose-500 dark:text-rose-400">*</span></label>
            <div class="grid grid-cols-2 gap-3" x-data="{ gender: '{{ old('jenis_kelamin', 'L') }}' }">
                <label class="p-3.5 rounded-2xl border cursor-pointer flex items-center justify-center gap-2 min-h-[48px] transition-all"
                       :class="gender === 'L' ? 'bg-sky-500/10 dark:bg-sky-500/20 border-sky-500 dark:border-sky-400 text-sky-700 dark:text-sky-300 font-extrabold shadow-md' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400'">
                    <input type="radio" name="jenis_kelamin" value="L" x-model="gender" class="sr-only">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Laki-Laki</span>
                </label>

                <label class="p-3.5 rounded-2xl border cursor-pointer flex items-center justify-center gap-2 min-h-[48px] transition-all"
                       :class="gender === 'P' ? 'bg-pink-500/10 dark:bg-pink-500/20 border-pink-500 dark:border-pink-400 text-pink-700 dark:text-pink-300 font-extrabold shadow-md' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400'">
                    <input type="radio" name="jenis_kelamin" value="P" x-model="gender" class="sr-only">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Perempuan</span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="berat_lahir_gram" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Berat Lahir (Gram) <span class="text-rose-500 dark:text-rose-400">*</span></label>
                <input type="number" id="berat_lahir_gram" name="berat_lahir_gram" value="{{ old('berat_lahir_gram', 3000) }}" inputmode="numeric" required
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 dark:focus:border-emerald-400 text-slate-900 dark:text-white rounded-2xl p-3.5 text-sm min-h-[48px] transition-all"
                       placeholder="Contoh: 3000">
            </div>

            <div>
                <label for="nama_orang_tua" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Nama Ibu / Orang Tua</label>
                <input type="text" id="nama_orang_tua" name="nama_orang_tua" value="{{ old('nama_orang_tua') }}"
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 focus:border-emerald-500 dark:focus:border-emerald-400 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-2xl p-3.5 text-sm min-h-[48px] transition-all"
                       placeholder="Nama Ibu Kandung">
            </div>
        </div>

        <!-- Radio Toggle: Status BBLR -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Riwayat BBLR (Berat Lahir Low &lt; 2500g) <span class="text-rose-500 dark:text-rose-400">*</span></label>
            <div class="grid grid-cols-3 gap-2.5" x-data="{ bblr: '{{ old('status_bblr', 'tidak') }}' }">
                <label class="p-3 rounded-2xl border cursor-pointer text-center min-h-[48px] flex items-center justify-center text-xs transition-all"
                       :class="bblr === 'tidak' ? 'bg-emerald-500/10 dark:bg-emerald-500/20 border-emerald-500 dark:border-emerald-400 text-emerald-700 dark:text-emerald-300 font-extrabold shadow-sm' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400'">
                    <input type="radio" name="status_bblr" value="tidak" x-model="bblr" class="sr-only">
                    <span>Tidak (Normal)</span>
                </label>

                <label class="p-3 rounded-2xl border cursor-pointer text-center min-h-[48px] flex items-center justify-center text-xs transition-all"
                       :class="bblr === 'bblr' ? 'bg-rose-500/10 dark:bg-rose-500/20 border-rose-500 dark:border-rose-400 text-rose-700 dark:text-rose-300 font-extrabold shadow-sm' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400'">
                    <input type="radio" name="status_bblr" value="bblr" x-model="bblr" class="sr-only">
                    <span>Ya (BBLR &lt;2500g)</span>
                </label>

                <label class="p-3 rounded-2xl border cursor-pointer text-center min-h-[48px] flex items-center justify-center text-xs transition-all"
                       :class="bblr === 'tidak_diketahui' ? 'bg-slate-200 dark:bg-slate-800 border-slate-400 dark:border-slate-600 text-slate-900 dark:text-slate-200 font-bold' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400'">
                    <input type="radio" name="status_bblr" value="tidak_diketahui" x-model="bblr" class="sr-only">
                    <span>Tidak Tahu</span>
                </label>
            </div>
        </div>

        <!-- Form Submit & Back Buttons -->
        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
            <a href="{{ route('balita.index') }}" class="px-5 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-2xl text-xs min-h-[44px] flex items-center transition-all">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-extrabold rounded-2xl shadow-lg shadow-emerald-500/20 active:scale-95 transition-all text-xs min-h-[44px] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Simpan Balita Baru</span>
            </button>
        </div>

    </form>
</div>

@endsection
