<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Anak;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;

// 1. Check as Guest
Auth::logout();
$anakGuest = Anak::withoutGlobalScope('posyandu_scope')->where('token_akses', 'BALITA-DAFFA-05')->first();
echo "Guest Access:\n";
echo 'Anak: '.($anakGuest ? $anakGuest->nama : 'NULL')."\n";
if ($anakGuest) {
    echo 'Pengukurans count: '.$anakGuest->pengukurans()->withoutGlobalScope('posyandu_scope')->count()."\n";
    echo 'Pengukurans with scope: '.$anakGuest->pengukurans->count()."\n";
}

// 2. Check as Kader 1 (Posyandu 1)
$kader1 = User::where('username', 'kader1')->first();
Auth::login($kader1);
echo "\nKader 1 (Posyandu 1) Accessing Daffa (Posyandu 2):\n";
$anakKader1 = Anak::withoutGlobalScope('posyandu_scope')->where('token_akses', 'BALITA-DAFFA-05')->first();
echo 'Anak: '.($anakKader1 ? $anakKader1->nama : 'NULL')."\n";
if ($anakKader1) {
    echo 'Pengukurans with scope: '.$anakKader1->pengukurans->count()."\n";
    echo 'Pengukurans without scope: '.$anakKader1->pengukurans()->withoutGlobalScope('posyandu_scope')->count()."\n";
}
