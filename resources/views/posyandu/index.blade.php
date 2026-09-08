@extends('layouts.app')

@section('title', 'Data Posyandu')
@section('page-title', 'Manajemen Data Posyandu')

@section('content')

<div class="space-y-6">

    <!-- Header Section -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl dark:shadow-black/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors duration-200">
        <div>
            <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">Daftar Unit Posyandu Wilayah</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola data unit Posyandu, wilayah, dan status aktif unit.</p>
        </div>

        @if(Auth::user()->isBidan())
            <button type="button" onclick="openTambahModal()" class="py-2.5 px-4 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-extrabold rounded-2xl text-xs flex items-center justify-center gap-2 min-h-[44px] shadow-lg active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Posyandu Baru</span>
            </button>
        @endif
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 dark:bg-rose-500/20 border border-rose-500/30 text-rose-800 dark:text-rose-300 text-xs font-bold space-y-1">
            <div class="flex items-center gap-2 font-black">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Terjadi Kesalahan Validasi:</span>
            </div>
            <ul class="list-disc list-inside pl-7 text-[11px]">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Posyandu Cards Grid (Non-aktif ditaruh di bawah) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($posyandus as $pos)
            <div class="bg-slate-50/90 dark:bg-slate-950/90 border {{ $pos->is_active ? 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700' : 'border-slate-200 dark:border-slate-800/50 opacity-75 bg-slate-100/50 dark:bg-slate-950/50' }} rounded-2xl p-5 space-y-4 relative overflow-hidden group transition-all shadow-sm">

                <!-- Card Header -->
                <div class="flex items-start justify-between">
                    <div class="w-11 h-11 rounded-2xl {{ $pos->is_active ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400' : 'bg-slate-200 dark:bg-slate-800/80 border-slate-300 dark:border-slate-700 text-slate-500' }} border flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0v-4a1 1 0 011-1h2a1 1 0 011 1v4m-4 0h4"/>
                        </svg>
                    </div>

                    <!-- Status Badge -->
                    <div class="flex items-center gap-2">
                        @if($pos->is_active)
                            <span class="px-2.5 py-1 bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30 text-[11px] font-black rounded-xl inline-flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                Aktif
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-700 text-[11px] font-bold rounded-xl inline-flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                Non-Aktif
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Posyandu Info -->
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">{{ $pos->nama }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-start gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span>{{ $pos->wilayah ?? 'Wilayah belum diatur' }}</span>
                    </p>
                </div>

                <!-- Counts Summary -->
                <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-200 dark:border-slate-800/80 text-xs">
                    <div class="bg-white dark:bg-slate-900/60 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800/60">
                        <span class="text-slate-500 dark:text-slate-400 block text-[10px]">Total Balita</span>
                        <span class="font-black text-slate-900 dark:text-white text-base mt-0.5 block">{{ number_format($pos->anaks_count ?? 0) }}</span>
                    </div>
                    <div class="bg-white dark:bg-slate-900/60 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800/60">
                        <span class="text-slate-500 dark:text-slate-400 block text-[10px]">Kader Terdaftar</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-base mt-0.5 block">{{ number_format($pos->users_count ?? 0) }}</span>
                    </div>
                </div>

                <!-- Bidan Action Buttons (Edit & Soft Toggle Status) -->
                @if(Auth::user()->isBidan())
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-end gap-2">
                        <!-- Edit Button -->
                        <button type="button"
                            onclick="openEditModal({{ $pos->id }}, '{{ addslashes($pos->nama) }}', '{{ addslashes($pos->wilayah ?? '') }}')"
                            class="py-1.5 px-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl border border-slate-200 dark:border-slate-700 flex items-center gap-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Edit</span>
                        </button>

                        <!-- Toggle Status Button (Soft Status) -->
                        <form action="{{ route('posyandu.toggle', $pos->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            @if($pos->is_active)
                                <button type="submit"
                                    onclick="return confirm('Apakah Anda yakin ingin menonaktifkan {{ addslashes($pos->nama) }}? Posyandu non-aktif tidak akan muncul di opsi pendaftaran balita/kader baru, tetapi data historis tetap tersimpan.')"
                                    class="py-1.5 px-3 bg-rose-500/10 hover:bg-rose-500/20 text-rose-700 dark:text-rose-300 border border-rose-500/30 font-bold text-xs rounded-xl flex items-center gap-1.5 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    <span>Nonaktifkan</span>
                                </button>
                            @else
                                <button type="submit"
                                    class="py-1.5 px-3 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30 font-bold text-xs rounded-xl flex items-center gap-1.5 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Aktifkan</span>
                                </button>
                            @endif
                        </form>
                    </div>
                @endif

            </div>
        @endforeach
    </div>

</div>

<!-- Modal Tambah Posyandu (Khusus Bidan) -->
@if(Auth::user()->isBidan())
<div id="modal-tambah-posyandu" class="hidden fixed inset-0 z-50 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4 transition-colors duration-200">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
            <h3 class="text-base font-black text-slate-900 dark:text-white">Tambah Posyandu Baru</h3>
            <button onclick="closeTambahModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center border border-slate-200 dark:border-slate-700 transition-colors">
                ✕
            </button>
        </div>

        <form action="{{ route('posyandu.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="tambah_nama" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Posyandu <span class="text-rose-600 dark:text-rose-400">*</span></label>
                <input type="text" id="tambah_nama" name="nama" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-2xl p-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-colors" placeholder="Contoh: Posyandu Dahlia 04">
            </div>

            <div>
                <label for="tambah_wilayah" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Wilayah / Alamat</label>
                <input type="text" id="tambah_wilayah" name="wilayah" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 rounded-2xl p-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-colors" placeholder="Contoh: Kelurahan Sukamaju RW 04">
            </div>

            <div class="pt-2 flex justify-end gap-2 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="closeTambahModal()" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold rounded-xl text-xs transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-extrabold rounded-xl text-xs shadow-md">Simpan Posyandu</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Posyandu (Khusus Bidan) -->
<div id="modal-edit-posyandu" class="hidden fixed inset-0 z-50 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4 transition-colors duration-200">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
            <h3 class="text-base font-black text-slate-900 dark:text-white">Edit Data Posyandu</h3>
            <button onclick="closeEditModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center border border-slate-200 dark:border-slate-700 transition-colors">
                ✕
            </button>
        </div>

        <form id="form-edit-posyandu" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_nama" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Posyandu <span class="text-rose-600 dark:text-rose-400">*</span></label>
                <input type="text" id="edit_nama" name="nama" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-2xl p-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-colors">
            </div>

            <div>
                <label for="edit_wilayah" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Wilayah / Alamat</label>
                <input type="text" id="edit_wilayah" name="wilayah" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-2xl p-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-colors">
            </div>

            <div class="pt-2 flex justify-end gap-2 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold rounded-xl text-xs transition-colors">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-extrabold rounded-xl text-xs shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
function openTambahModal() {
    document.getElementById('modal-tambah-posyandu').classList.remove('hidden');
    document.getElementById('tambah_nama').focus();
}

function closeTambahModal() {
    document.getElementById('modal-tambah-posyandu').classList.add('hidden');
}

function openEditModal(id, nama, wilayah) {
    const form = document.getElementById('form-edit-posyandu');
    form.action = "/posyandu/" + id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_wilayah').value = wilayah;
    document.getElementById('modal-edit-posyandu').classList.remove('hidden');
    document.getElementById('edit_nama').focus();
}

function closeEditModal() {
    document.getElementById('modal-edit-posyandu').classList.add('hidden');
}
</script>

@endsection
