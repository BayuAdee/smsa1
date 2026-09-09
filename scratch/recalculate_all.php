<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\SawCalculatorService;
use Illuminate\Support\Facades\DB;

echo "==========================================================" . PHP_EOL;
echo " MENGHITUNG ULANG HASIL SAW DENGAN BOBOT BARU (45-25-20-10)" . PHP_EOL;
echo "==========================================================" . PHP_EOL;

$periods = DB::table('pengukurans')
    ->select('posyandu_id', 'bulan_ukur', 'tahun_ukur')
    ->join('anaks', 'anaks.id', '=', 'pengukurans.anak_id')
    ->distinct()
    ->get();

$sawService = app(SawCalculatorService::class);
$count = 0;

foreach ($periods as $p) {
    if (!$p->bulan_ukur || !$p->tahun_ukur) continue;
    $sawService->hitungUntukPosyanduPeriode($p->posyandu_id, (int)$p->bulan_ukur, (int)$p->tahun_ukur);
    $count++;
}

echo "SUKSES: Seluruh {$count} kombinasi periode Posyandu telah dihitung ulang dengan bobot baru!" . PHP_EOL;
