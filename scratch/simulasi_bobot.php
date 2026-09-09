<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Anak;
use App\Models\Pengukuran;
use App\Services\GrowthFalteringService;
use App\Services\ZscoreService;
use Illuminate\Contracts\Console\Kernel;

$zscoreService = app(ZscoreService::class);
$growthService = app(GrowthFalteringService::class);

echo '=========================================================='.PHP_EOL;
echo ' PERBANDINGAN 3 SKEMA BOBOT SAW (JULI 2026)                '.PHP_EOL;
echo '=========================================================='.PHP_EOL;

$bulan = 7;
$tahun = 2026;

$anaks = Anak::withoutGlobalScopes()->where('status_aktif', true)->get();

$opt1 = ['w1' => 0.40, 'w2' => 0.25, 'w3' => 0.25, 'w4' => 0.10]; // Option 1: 40-25-25-10
$opt2 = ['w1' => 0.45, 'w2' => 0.30, 'w3' => 0.15, 'w4' => 0.10]; // Option 2: 45-30-15-10
$opt3 = ['w1' => 0.45, 'w2' => 0.25, 'w3' => 0.20, 'w4' => 0.10]; // Option 3: 45-25-20-10

$list1 = [];
$list2 = [];
$list3 = [];

foreach ($anaks as $anak) {
    $pengukuran = Pengukuran::withoutGlobalScopes()
        ->where('anak_id', $anak->id)
        ->where('bulan_ukur', $bulan)
        ->where('tahun_ukur', $tahun)
        ->first();

    if (! $pengukuran) {
        continue;
    }

    // C1: TB/U
    $zTbu = $zscoreService->calculate('tbu', $anak->jenis_kelamin, (int) $pengukuran->usia_bulan, (float) $pengukuran->tinggi_cm);
    if ($zTbu >= -2.0) {
        $rawC1 = 1.0;
    } elseif ($zTbu >= -3.0) {
        $rawC1 = round(2.0 + (-2.0 - $zTbu), 4);
    } else {
        $rawC1 = min(4.0, round(3.0 + (-3.0 - $zTbu), 4));
    }

    // C2: Growth Faltering
    $c2Data = $growthService->evaluate($anak, $pengukuran);
    $rawC2 = (float) $c2Data['skor'];

    // C3: BB/U
    $zBbu = $zscoreService->calculate('bbu', $anak->jenis_kelamin, (int) $pengukuran->usia_bulan, (float) $pengukuran->berat_kg);
    if ($zBbu >= -2.0 && $zBbu <= 1.0) {
        $rawC3 = 1.0;
    } elseif ($zBbu >= -3.0 && $zBbu < -2.0) {
        $rawC3 = round(2.0 + (-2.0 - $zBbu), 4);
    } elseif ($zBbu < -3.0) {
        $rawC3 = min(4.0, round(3.0 + (-3.0 - $zBbu), 4));
    } else {
        $rawC3 = min(2.0, round(1.0 + (($zBbu - 1.0) * 0.5), 4));
    }

    // C4: BBLR
    if ($anak->status_bblr === 'bblr' || ($anak->berat_lahir_gram && $anak->berat_lahir_gram < 2500)) {
        $rawC4 = 4.0;
    } elseif ($anak->status_bblr === 'tidak_diketahui' || empty($anak->berat_lahir_gram)) {
        $rawC4 = 2.0;
    } else {
        $rawC4 = 1.0;
    }

    $r1 = round(1.0 / max(1.0, $rawC1), 4);
    $r2 = round(1.0 / max(1.0, $rawC2), 4);
    $r3 = round(1.0 / max(1.0, $rawC3), 4);
    $r4 = round(1.0 / max(1.0, $rawC4), 4);

    $v1 = round(($opt1['w1'] * $r1) + ($opt1['w2'] * $r2) + ($opt1['w3'] * $r3) + ($opt1['w4'] * $r4), 4);
    $v2 = round(($opt2['w1'] * $r1) + ($opt2['w2'] * $r2) + ($opt2['w3'] * $r3) + ($opt2['w4'] * $r4), 4);
    $v3 = round(($opt3['w1'] * $r1) + ($opt3['w2'] * $r2) + ($opt3['w3'] * $r3) + ($opt3['w4'] * $r4), 4);

    $list1[] = ['nama' => $anak->nama, 'v' => $v1];
    $list2[] = ['nama' => $anak->nama, 'v' => $v2];
    $list3[] = ['nama' => $anak->nama, 'v' => $v3];
}

usort($list1, fn ($a, $b) => $a['v'] <=> $b['v']);
usort($list2, fn ($a, $b) => $a['v'] <=> $b['v']);
usort($list3, fn ($a, $b) => $a['v'] <=> $b['v']);

echo sprintf('%-4s | %-28s | %-12s | %-12s | %-12s', 'Rank', 'Nama Anak', 'Skema Awal', 'Opsi 1 (30/15)', 'Opsi 2 (25/20)').PHP_EOL;
echo sprintf('%-4s | %-28s | %-12s | %-12s | %-12s', '', '', '(40-25-25-10)', '(45-30-15-10)', '(45-25-20-10)').PHP_EOL;
echo str_repeat('-', 80).PHP_EOL;

for ($i = 0; $i < min(12, count($list3)); $i++) {
    $item3 = $list3[$i];

    // Find rank in list1 and list2
    $r1 = 0;
    $r2 = 0;
    foreach ($list1 as $idx => $o) {
        if ($o['nama'] === $item3['nama']) {
            $r1 = $idx + 1;
            break;
        }
    }
    foreach ($list2 as $idx => $o) {
        if ($o['nama'] === $item3['nama']) {
            $r2 = $idx + 1;
            break;
        }
    }

    $r3 = $i + 1;
    echo sprintf('%-4d | %-28s | V=%.4f (#%d) | V=%.4f (#%d) | V=%.4f (#%d)',
        $r3, substr($item3['nama'], 0, 28),
        $list1[$r1 - 1]['v'], $r1,
        $list2[$r2 - 1]['v'], $r2,
        $item3['v'], $r3
    ).PHP_EOL;
}
