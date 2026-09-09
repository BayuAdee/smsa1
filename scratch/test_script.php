<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Anak;
use App\Models\Posyandu;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;

echo '=== MEMERIKSA DATA ANAK & POSYANDU DALAM DATABASE ==='.PHP_EOL;

$totalAnak = Anak::withoutGlobalScopes()->count();
echo 'Total Anak di DB: '.$totalAnak.PHP_EOL;

$posyandus = Posyandu::all();
echo 'Daftar Posyandu:'.PHP_EOL;
foreach ($posyandus as $p) {
    echo "- ID: {$p->id} | Nama: {$p->nama}".PHP_EOL;
}

echo PHP_EOL.'=== MEMBACA FILE CSV UNTUK SIMULASI IMPORT ==='.PHP_EOL;

$csvFile = __DIR__.'/test_import.csv';
if (! file_exists($csvFile)) {
    echo 'File test_import.csv tidak ditemukan!'.PHP_EOL;
    exit;
}

$handle = fopen($csvFile, 'r');
// Read header
$header = fgetcsv($handle);
print_r($header);

$monthsMap = [
    'januari' => 1,
    'februari' => 2,
    'maret' => 3,
    'april' => 4,
    'mei' => 5,
    'juni' => 6,
    'juli' => 7,
    'agustus' => 8,
    'september' => 9,
    'oktober' => 10,
    'november' => 11,
    'desember' => 12,
];

$year = 2026;

$rowNum = 1;
while (($data = fgetcsv($handle)) !== false) {
    $rowNum++;
    $row = array_combine($header, $data);
    $nama = trim($row['Nama']);
    $posyanduInput = trim($row['Posyandu']);

    echo PHP_EOL."Baris #{$rowNum}: Anak '{$nama}', Posyandu '{$posyanduInput}'".PHP_EOL;

    // Search Posyandu
    $posyanduObj = Posyandu::where('nama', 'like', "%{$posyanduInput}%")
        ->orWhere('nama', 'like', '%'.str_replace('Posyandu ', '', $posyanduInput).'%')
        ->first();

    if (! $posyanduObj) {
        echo "  [WARN] Posyandu '{$posyanduInput}' tidak ditemukan di DB.".PHP_EOL;
    } else {
        echo "  [OK] Posyandu ditemukan: {$posyanduObj->nama} (ID: {$posyanduObj->id})".PHP_EOL;
    }

    // Search Anak
    $anakQuery = Anak::withoutGlobalScopes()->where('nama', 'like', $nama);
    if ($posyanduObj) {
        $anakQuery->where('posyandu_id', $posyanduObj->id);
    }
    $anak = $anakQuery->first();

    if (! $anak) {
        // Try fuzzy search or without posyandu filter
        $anak = Anak::withoutGlobalScopes()->where('nama', 'like', '%'.$nama.'%')->first();
    }

    if ($anak) {
        echo "  [OK] Anak ditemukan di DB: ID {$anak->id}, Nama: '{$anak->nama}', Tgl Lahir: {$anak->tanggal_lahir->format('Y-m-d')}".PHP_EOL;

        // Process monthly measurements
        foreach ($monthsMap as $bulanNama => $bulanAngka) {
            $colBerat = 'Berat_'.ucfirst($bulanNama);
            $colTinggi = 'Tinggi_'.ucfirst($bulanNama);

            $beratVal = isset($row[$colBerat]) && trim($row[$colBerat]) !== '' ? (float) str_replace(',', '.', trim($row[$colBerat])) : null;
            $tinggiVal = isset($row[$colTinggi]) && trim($row[$colTinggi]) !== '' ? (float) str_replace(',', '.', trim($row[$colTinggi])) : null;

            if ($beratVal !== null && $tinggiVal !== null && $beratVal > 0 && $tinggiVal > 0) {
                // Calculate age in months at measurement date (15th of the month)
                $tglUkur = Carbon::createFromDate($year, $bulanAngka, 15);
                $tglLahir = Carbon::parse($anak->tanggal_lahir);
                $usiaBulan = (int) $tglLahir->diffInMonths($tglUkur);

                echo '    -> Bulan '.ucfirst($bulanNama)." ({$bulanAngka}/{$year}): Usia {$usiaBulan} bln | BB: {$beratVal} kg | TB: {$tinggiVal} cm".PHP_EOL;
            } else {
                echo '    -> Bulan '.ucfirst($bulanNama).': [KOSONG / DIABAIKAN]'.PHP_EOL;
            }
        }
    } else {
        echo "  [ERR] Anak '{$nama}' TIDAK ditemukan di DB.".PHP_EOL;
    }
}

fclose($handle);
