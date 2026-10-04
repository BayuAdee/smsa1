<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalitaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\KaderController;
use App\Http\Controllers\OrtuController;
use App\Http\Controllers\PengukuranController;
use App\Http\Controllers\PosyanduController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\SettingSpkController;
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

// Lupa Password (Khusus Bidan Desa)
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:5,60')->name('password.email');
Route::get('/reset-password', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->middleware('throttle:10,60')->name('password.update');

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
    Route::get('/balita/search', [BalitaController::class, 'search'])->name('balita.search');
    Route::get('/balita/create', [BalitaController::class, 'create'])->name('balita.create');
    Route::post('/balita', [BalitaController::class, 'store'])->name('balita.store');
    Route::get('/balita/{id}', [BalitaController::class, 'show'])->name('balita.show');
    Route::get('/balita/{id}/edit', [BalitaController::class, 'edit'])->name('balita.edit');
    Route::put('/balita/{id}', [BalitaController::class, 'update'])->name('balita.update');
    Route::delete('/balita/{id}', [BalitaController::class, 'destroy'])->name('balita.destroy');
    Route::patch('/balita/{id}/toggle-archive', [BalitaController::class, 'toggleArchive'])->name('balita.toggle-archive');

    // Input Pengukuran Bulanan
    Route::get('/pengukuran', [PengukuranController::class, 'index'])->name('pengukuran.index');
    Route::get('/pengukuran/create', [PengukuranController::class, 'create'])->name('pengukuran.create');
    Route::post('/pengukuran', [PengukuranController::class, 'store'])->name('pengukuran.store');
    Route::delete('/pengukuran', [PengukuranController::class, 'destroy'])->name('pengukuran.destroy');

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
    Route::get('/kader/search', [KaderController::class, 'search'])->name('kader.search');
    Route::get('/kader/create', [KaderController::class, 'create'])->name('kader.create');
    Route::post('/kader', [KaderController::class, 'store'])->name('kader.store');
    Route::get('/kader/{id}/edit', [KaderController::class, 'edit'])->name('kader.edit');
    Route::put('/kader/{id}', [KaderController::class, 'update'])->name('kader.update');
    Route::delete('/kader/{id}', [KaderController::class, 'destroy'])->name('kader.destroy');

    // Import & Export Data Dedicated Routes
    Route::get('/import-export', [ImportExportController::class, 'index'])->name('import-export.index');
    Route::get('/import-export/template', [ImportExportController::class, 'downloadTemplate'])->name('import-export.template');
    Route::get('/import-export/download-failed', [ImportExportController::class, 'downloadFailedLog'])->name('import-export.download-failed');
    Route::post('/import-export/preview', [ImportExportController::class, 'previewImport'])->name('import-export.preview');
    Route::post('/import-export/execute', [ImportExportController::class, 'executeImport'])->name('import-export.execute');
    Route::get('/import-export/export', [ImportExportController::class, 'export'])->name('import-export.export');

    // Pengaturan Parameter SPK SAW (Khusus Bidan Desa)
    Route::get('/pengaturan/spk', [SettingSpkController::class, 'index'])->name('settings.spk.index');
    Route::put('/pengaturan/spk', [SettingSpkController::class, 'update'])->name('settings.spk.update');
    Route::post('/pengaturan/spk/reset', [SettingSpkController::class, 'resetDefault'])->name('settings.spk.reset');
}
);
