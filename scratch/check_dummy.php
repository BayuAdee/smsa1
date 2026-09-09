<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Anak;
use App\Models\HasilSaw;
use App\Models\Pengukuran;

$budi = Anak::withoutGlobalScopes()->where('nama', 'like', '%budi%dummy%')->first();
$cici = Anak::withoutGlobalScopes()->where('nama', 'like', '%cici%dummy%')->first();

echo "=== PROFILE ANAK DUMMY ===" . PHP_EOL;
if ($budi) {
    echo "BUDI: ID {$budi->id} | Nama: {$budi->nama} | Tgl Lahir: {$budi->tanggal_lahir} | JK: {$budi->jenis_kelamin} | BBLR: {$budi->status_bblr} | BB Lahir: {$budi->berat_lahir_gram}g" . PHP_EOL;
}
if ($cici) {
    echo "CICI: ID {$cici->id} | Nama: {$cici->nama} | Tgl Lahir: {$cici->tanggal_lahir} | JK: {$cici->jenis_kelamin} | BBLR: {$cici->status_bblr} | BB Lahir: {$cici->berat_lahir_gram}g" . PHP_EOL;
}

echo PHP_EOL . "=== PENGUKURAN & HASIL SAW DUMMY PER BULAN ===" . PHP_EOL;

if ($budi && $cici) {
    $bulanList = [1, 2, 3, 4, 5, 6, 7, 8];
    foreach ($bulanList as $b) {
        echo "--- BULAN {$b}/2026 ---" . PHP_EOL;

        $pBudi = Pengukuran::withoutGlobalScopes()->where('anak_id', $budi->id)->where('bulan_ukur', $b)->where('tahun_ukur', 2026)->first();
        $pCici = Pengukuran::withoutGlobalScopes()->where('anak_id', $cici->id)->where('bulan_ukur', $b)->where('tahun_ukur', 2026)->first();

        $hBudi = HasilSaw::withoutGlobalScopes()->where('anak_id', $budi->id)->where('bulan_ukur', $b)->where('tahun_ukur', 2026)->first();
        $hCici = HasilSaw::withoutGlobalScopes()->where('anak_id', $cici->id)->where('bulan_ukur', $b)->where('tahun_ukur', 2026)->first();

        if ($pBudi) {
            echo "  BUDI : TB={$pBudi->tinggi_cm}cm, BB={$pBudi->berat_kg}kg | Z-TB/U=" . ($hBudi->z_tbu ?? '-') . ", Z-BB/U=" . ($hBudi->z_bbu ?? '-') . " | C1={$hBudi->raw_c1}, C2={$hBudi->raw_c2}, C3={$hBudi->raw_c3}, C4={$hBudi->raw_c4} => V=" . ($hBudi->nilai_v ?? '-') . " ({$hBudi->kategori_risiko})" . PHP_EOL;
        } else {
            echo "  BUDI : Tidak ada pengukuran." . PHP_EOL;
        }

        if ($pCici) {
            echo "  CICI : TB={$pCici->tinggi_cm}cm, BB={$pCici->berat_kg}kg | Z-TB/U=" . ($hCici->z_tbu ?? '-') . ", Z-BB/U=" . ($hCici->z_bbu ?? '-') . " | C1={$hCici->raw_c1}, C2={$hCici->raw_c2}, C3={$hCici->raw_c3}, C4={$hCici->raw_c4} => V=" . ($hCici->nilai_v ?? '-') . " ({$hCici->kategori_risiko})" . PHP_EOL;
        } else {
            echo "  CICI : Tidak ada pengukuran." . PHP_EOL;
        }
    }
}
