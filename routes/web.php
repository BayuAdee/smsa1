<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalitaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaderController;
use App\Http\Controllers\OrtuController;
use App\Http\Controllers\PengukuranController;
use App\Http\Controllers\PosyanduController;
use App\Http\Controllers\RankingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pintu 1: Akses Publik / Orang Tua (Read-Only without login)
|--------------------------------------------------------------------------
*/
Route::get('/', [OrtuController::class, 'landing'])->name('landing');
Route::post('/ortu/check', [OrtuController::class, 'checkToken'])->name('ortu.check');
Route::get('/ortu/{token}', [OrtuController::class, 'show'])->name('ortu.show');

/*
|--------------------------------------------------------------------------
| Pintu 2: Autentikasi (Petugas Kader & Bidan)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Pintu 3: Internal Dashboard Petugas (Protected by auth middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Select Posyandu Filter (Khusus Bidan)
    Route::post('/select-posyandu', [AuthController::class, 'selectPosyandu'])->name('select-posyandu');

    // Dashboard Utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Data Balita (CRUD)
    Route::get('/balita', [BalitaController::class, 'index'])->name('balita.index');
    Route::get('/balita/create', [BalitaController::class, 'create'])->name('balita.create');
    Route::post('/balita', [BalitaController::class, 'store'])->name('balita.store');
    Route::get('/balita/{id}', [BalitaController::class, 'show'])->name('balita.show');
    Route::get('/balita/{id}/edit', [BalitaController::class, 'edit'])->name('balita.edit');
    Route::put('/balita/{id}', [BalitaController::class, 'update'])->name('balita.update');
    Route::delete('/balita/{id}', [BalitaController::class, 'destroy'])->name('balita.destroy');

    // Input Pengukuran Bulanan
    Route::get('/pengukuran', [PengukuranController::class, 'index'])->name('pengukuran.index');
    Route::get('/pengukuran/create', [PengukuranController::class, 'create'])->name('pengukuran.create');
    Route::post('/pengukuran', [PengukuranController::class, 'store'])->name('pengukuran.store');

    // Ranking Prioritas Stunting (Tabel SAW)
    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
    Route::post('/ranking/recalculate', [RankingController::class, 'recalculate'])->name('ranking.recalculate');

    // Data Posyandu
    Route::get('/posyandu', [PosyanduController::class, 'index'])->name('posyandu.index');
    Route::post('/posyandu', [PosyanduController::class, 'store'])->name('posyandu.store');
    Route::put('/posyandu/{id}', [PosyanduController::class, 'update'])->name('posyandu.update');
    Route::patch('/posyandu/{id}/toggle', [PosyanduController::class, 'toggleStatus'])->name('posyandu.toggle');

    // Kelola Data Kader (CRUD - Khusus Bidan Desa)
    Route::get('/kader', [KaderController::class, 'index'])->name('kader.index');
    Route::get('/kader/create', [KaderController::class, 'create'])->name('kader.create');
    Route::post('/kader', [KaderController::class, 'store'])->name('kader.store');
    Route::get('/kader/{id}/edit', [KaderController::class, 'edit'])->name('kader.edit');
    Route::put('/kader/{id}', [KaderController::class, 'update'])->name('kader.update');
    Route::delete('/kader/{id}', [KaderController::class, 'destroy'])->name('kader.destroy');
});
