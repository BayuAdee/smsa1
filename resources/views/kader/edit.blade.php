@extends('layouts.app')

@section('title', 'Edit Data Kader')
@section('page-title', 'Pembaruan Data Petugas Kader')

@section('content')

<div class="max-w-2xl mx-auto bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
    
    <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
        <div>
            <h2 class="text-lg font-extrabold text-white">Edit Akun Kader: {{ $kader->name }}</h2>
            <p class="text-xs text-slate-400 mt-1">Perbarui informasi profil atau ubah penugasan Posyandu kader.</p>
        </div>
        <a href="{{ route('kader.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition-all border border-slate-700">
            Kembali
        </a>
    </div>

    <form action="{{ route('kader.update', $kader->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <!-- Nama Lengkap -->
        <div>
            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Nama Lengkap Kader <span class="text-rose-400">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $kader->name) }}" required 
                   class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3.5 text-sm focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400">
        </div>

        <!-- Posyandu Penugasan -->
        <div>
            <label for="posyandu_id" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Unit Posyandu Penugasan <span class="text-rose-400">*</span></label>
            <select id="posyandu_id" name="posyandu_id" required 
                    class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3.5 text-sm focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 font-semibold cursor-pointer">
                @foreach($posyandus as $pos)
                    <option value="{{ $pos->id }}" {{ old('posyandu_id', $kader->posyandu_id) == $pos->id ? 'selected' : '' }}>
                        {{ $pos->nama }} ({{ $pos->wilayah ?? 'Tanpa Wilayah' }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Username & Email Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Username Login <span class="text-rose-400">*</span></label>
                <input type="text" id="username" name="username" value="{{ old('username', $kader->username) }}" required 
                       class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3.5 text-sm focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 font-mono">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Alamat Email <span class="text-slate-500">(Opsional)</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $kader->email) }}" 
                       class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3.5 text-sm focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400">
            </div>
        </div>

        <!-- Password Baru (Opsional) -->
        <div>
            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Password Baru <span class="text-slate-500">(Opsional)</span></label>
            <input type="password" id="password" name="password" 
                   class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3.5 text-sm focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400"
                   placeholder="Kosongkan jika tidak ingin mengubah password">
            <p class="text-[11px] text-slate-500 mt-1">Isi hanya jika ingin mengganti password akun kader ini (minimal 6 karakter).</p>
        </div>

        <!-- Submit Button Group -->
        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800/80">
            <a href="{{ route('kader.index') }}" class="px-5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs transition-all">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-extrabold rounded-xl text-xs shadow-lg shadow-emerald-500/20 hover:brightness-110 transition-all">
                Simpan Perubahan
            </button>
        </div>

    </form>

</div>

@endsection
