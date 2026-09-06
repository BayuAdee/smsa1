@extends('layouts.app')

@section('title', 'Edit Balita')
@section('page-title', 'Edit Data Balita')

@section('content')

<div class="max-w-2xl mx-auto bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-8 shadow-xl">
    
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-white">Edit Data Balita: {{ $anak->nama }}</h2>
            <p class="text-xs text-slate-400 mt-1">Perbarui data identitas dan status kelahiran anak.</p>
        </div>
        <span class="px-3 py-1 bg-slate-950 border border-slate-800 font-mono text-emerald-400 text-xs font-bold rounded-xl">
            {{ $anak->token_akses }}
        </span>
    </div>

    <form action="{{ route('balita.update', $anak->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        @if(Auth::user()->isBidan())
            <div>
                <label for="posyandu_id" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Posyandu Wilayah</label>
                <select name="posyandu_id" id="posyandu_id" required class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-400 text-white rounded-2xl p-3.5 text-sm min-h-[48px]">
                    @foreach($posyandus as $pos)
                        <option value="{{ $pos->id }}" {{ old('posyandu_id', $anak->posyandu_id) == $pos->id ? 'selected' : '' }}>
                            {{ $pos->nama }} ({{ $pos->wilayah }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label for="nama" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Nama Lengkap Balita <span class="text-rose-400">*</span></label>
            <input type="text" id="nama" name="nama" value="{{ old('nama', $anak->nama) }}" required 
                   class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 text-white rounded-2xl p-3.5 text-sm min-h-[48px]">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="nik" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">NIK</label>
                <input type="text" id="nik" name="nik" value="{{ old('nik', $anak->nik) }}" inputmode="numeric"
                       class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-400 text-white rounded-2xl p-3.5 text-sm min-h-[48px]">
            </div>

            <div>
                <label for="tanggal_lahir" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Tanggal Lahir <span class="text-rose-400">*</span></label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $anak->tanggal_lahir->format('Y-m-d')) }}" required 
                       class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-400 text-white rounded-2xl p-3.5 text-sm min-h-[48px]">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Jenis Kelamin <span class="text-rose-400">*</span></label>
            <div class="grid grid-cols-2 gap-3" x-data="{ gender: '{{ old('jenis_kelamin', $anak->jenis_kelamin) }}' }">
                <label class="p-3.5 rounded-2xl border cursor-pointer flex items-center justify-center gap-2 min-h-[48px] transition-all"
                       :class="gender === 'L' ? 'bg-sky-500/20 border-sky-400 text-sky-300 font-extrabold shadow-md' : 'bg-slate-950 border-slate-700 text-slate-400'">
                    <input type="radio" name="jenis_kelamin" value="L" x-model="gender" class="sr-only">
                    <span>Laki-Laki</span>
                </label>

                <label class="p-3.5 rounded-2xl border cursor-pointer flex items-center justify-center gap-2 min-h-[48px] transition-all"
                       :class="gender === 'P' ? 'bg-pink-500/20 border-pink-400 text-pink-300 font-extrabold shadow-md' : 'bg-slate-950 border-slate-700 text-slate-400'">
                    <input type="radio" name="jenis_kelamin" value="P" x-model="gender" class="sr-only">
                    <span>Perempuan</span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="berat_lahir_gram" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Berat Lahir (Gram)</label>
                <input type="number" id="berat_lahir_gram" name="berat_lahir_gram" value="{{ old('berat_lahir_gram', $anak->berat_lahir_gram) }}" inputmode="numeric" required 
                       class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-400 text-white rounded-2xl p-3.5 text-sm min-h-[48px]">
            </div>

            <div>
                <label for="nama_orang_tua" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Nama Ibu / Orang Tua</label>
                <input type="text" id="nama_orang_tua" name="nama_orang_tua" value="{{ old('nama_orang_tua', $anak->nama_orang_tua) }}"
                       class="w-full bg-slate-950 border border-slate-700 focus:border-emerald-400 text-white rounded-2xl p-3.5 text-sm min-h-[48px]">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Riwayat BBLR (&lt; 2500g)</label>
            <div class="grid grid-cols-3 gap-2.5" x-data="{ bblr: '{{ old('status_bblr', $anak->status_bblr) }}' }">
                <label class="p-3 rounded-2xl border cursor-pointer text-center min-h-[48px] flex items-center justify-center text-xs transition-all"
                       :class="bblr === 'tidak' ? 'bg-emerald-500/20 border-emerald-400 text-emerald-300 font-extrabold' : 'bg-slate-950 border-slate-700 text-slate-400'">
                    <input type="radio" name="status_bblr" value="tidak" x-model="bblr" class="sr-only">
                    <span>Tidak (Normal)</span>
                </label>

                <label class="p-3 rounded-2xl border cursor-pointer text-center min-h-[48px] flex items-center justify-center text-xs transition-all"
                       :class="bblr === 'bblr' ? 'bg-rose-500/20 border-rose-400 text-rose-300 font-extrabold' : 'bg-slate-950 border-slate-700 text-slate-400'">
                    <input type="radio" name="status_bblr" value="bblr" x-model="bblr" class="sr-only">
                    <span>Ya (BBLR)</span>
                </label>

                <label class="p-3 rounded-2xl border cursor-pointer text-center min-h-[48px] flex items-center justify-center text-xs transition-all"
                       :class="bblr === 'tidak_diketahui' ? 'bg-slate-800 border-slate-600 text-slate-200 font-bold' : 'bg-slate-950 border-slate-700 text-slate-400'">
                    <input type="radio" name="status_bblr" value="tidak_diketahui" x-model="bblr" class="sr-only">
                    <span>Tidak Tahu</span>
                </label>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
            <button type="button" onclick="if(confirm('Yakin ingin menghapus data balita ini?')) document.getElementById('delete-form').submit();" class="px-4 py-3 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold rounded-2xl text-xs min-h-[44px]">
                Hapus Data
            </button>

            <div class="flex items-center gap-3">
                <a href="{{ route('balita.index') }}" class="px-5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-2xl text-xs min-h-[44px] flex items-center">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 text-slate-950 font-extrabold rounded-2xl shadow-lg text-xs min-h-[44px]">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" action="{{ route('balita.destroy', $anak->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

@endsection
