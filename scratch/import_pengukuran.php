<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Anak;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use App\Models\User;
use App\Services\SawCalculatorService;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$fileArg = $argv[1] ?? __DIR__.'/Hasil_Pengukuran_Posyandu_Gabungan_2026.csv';
$yearArg = isset($argv[2]) ? (int) $argv[2] : 2026;

echo '=========================================================='.PHP_EOL;
echo ' PROSES IMPORT DATA PENGUKURAN GABUNGAN 2026 (MODE A)     '.PHP_EOL;
echo '=========================================================='.PHP_EOL;
echo 'File Target  : '.$fileArg.PHP_EOL;
echo 'Tahun Ukur   : '.$yearArg.PHP_EOL;

if (! file_exists($fileArg)) {
    echo "ERROR: File '{$fileArg}' tidak ditemukan!".PHP_EOL;
    exit(1);
}

$defaultUser = User::where('role', 'bidan')->first() ?? User::first();

$handle = fopen($fileArg, 'r');
$bom = fread($handle, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($handle);
}

$firstLine = fgets($handle);
$delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
rewind($handle);
if ($bom === "\xEF\xBB\xBF") {
    fseek($handle, 3);
}

$header = fgetcsv($handle, 4096, $delimiter);
if (! $header) {
    echo 'ERROR: Header file CSV tidak dapat dibaca.'.PHP_EOL;
    exit(1);
}

$header = array_map('trim', $header);

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

$importedCount = 0;
$skippedChildren = [];
$matchedChildren = 0;
$affectedPeriods = [];

DB::beginTransaction();

try {
    $rowNum = 1;
    while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
        $rowNum++;
        if (empty(array_filter($data))) {
            continue;
        }

        if (count($data) < count($header)) {
            $data = array_pad($data, count($header), '');
        }

        $row = array_combine($header, array_slice($data, 0, count($header)));
        $nama = trim($row['Nama'] ?? '');
        $posyanduInput = trim($row['Posyandu'] ?? '');

        if (empty($nama) || empty($posyanduInput)) {
            continue;
        }

        $posyanduObj = Posyandu::where('nama', 'like', "%{$posyanduInput}%")
            ->orWhere('nama', 'like', '%'.str_replace('Posyandu ', '', $posyanduInput).'%')
            ->first();

        if (! $posyanduObj) {
            $skippedChildren[] = "Baris #{$rowNum}: Posyandu '{$posyanduInput}' tidak ditemukan (Anak: {$nama})";

            continue;
        }

        // Clean quotes for search
        $cleanNamePattern = str_replace(["'", '\\', '"'], '%', $nama);

        $anak = Anak::withoutGlobalScopes()
            ->where('posyandu_id', $posyanduObj->id)
            ->where(function ($q) use ($nama, $cleanNamePattern) {
                $q->where('nama', 'like', $nama)
                    ->orWhere('nama', 'like', $cleanNamePattern);
            })
            ->first();

        if (! $anak) {
            $anak = Anak::withoutGlobalScopes()
                ->where('nama', 'like', $nama)
                ->orWhere('nama', 'like', $cleanNamePattern)
                ->first();
        }

        if (! $anak) {
            $skippedChildren[] = "Baris #{$rowNum}: Anak '{$nama}' tidak ditemukan di DB ({$posyanduObj->nama})";

            continue;
        }

        $matchedChildren++;
        $creatorUser = User::where('posyandu_id', $posyanduObj->id)->first() ?? $defaultUser;

        foreach ($monthsMap as $bulanNama => $bulanAngka) {
            $colBerat = 'Berat_'.ucfirst($bulanNama);
            $colTinggi = 'Tinggi_'.ucfirst($bulanNama);

            $rawBerat = isset($row[$colBerat]) ? trim($row[$colBerat]) : '';
            $rawTinggi = isset($row[$colTinggi]) ? trim($row[$colTinggi]) : '';

            if ($rawBerat === '' || $rawTinggi === '') {
                continue;
            }

            $beratVal = (float) str_replace(',', '.', $rawBerat);
            $tinggiVal = (float) str_replace(',', '.', $rawTinggi);

            if ($beratVal <= 0 || $tinggiVal <= 0) {
                continue;
            }

            $tglUkur = Carbon::createFromDate($yearArg, $bulanAngka, 15);
            $tglLahir = Carbon::parse($anak->tanggal_lahir);
            $usiaBulan = (int) $tglLahir->diffInMonths($tglUkur);

            // Mode A: Update Or Create
            Pengukuran::withoutGlobalScopes()->updateOrCreate(
                [
                    'anak_id' => $anak->id,
                    'bulan_ukur' => $bulanAngka,
                    'tahun_ukur' => $yearArg,
                ],
                [
                    'tanggal_ukur' => $tglUkur->format('Y-m-d'),
                    'usia_bulan' => $usiaBulan,
                    'tinggi_cm' => $tinggiVal,
                    'berat_kg' => $beratVal,
                    'dibuat_oleh' => $creatorUser->id,
                ]
            );

            $importedCount++;
            $affectedPeriods["{$posyanduObj->id}_{$bulanAngka}_{$yearArg}"] = [
                'posyandu_id' => $posyanduObj->id,
                'bulan' => $bulanAngka,
                'tahun' => $yearArg,
            ];
        }
    }

    fclose($handle);
    DB::commit();

    echo PHP_EOL.'=== MENGHITUNG ULANG Z-SCORE & SAW PARALLEL ==='.PHP_EOL;
    $sawService = app(SawCalculatorService::class);
    foreach ($affectedPeriods as $period) {
        $sawService->hitungUntukPosyanduPeriode($period['posyandu_id'], $period['bulan'], $period['tahun']);
        echo "  + Recalculated: Posyandu ID {$period['posyandu_id']} Periode {$period['bulan']}/{$period['tahun']}".PHP_EOL;
    }

    echo PHP_EOL.'=========================================================='.PHP_EOL;
    echo ' RINGKASAN PROSES IMPORT GABUNGAN:'.PHP_EOL;
    echo " - Total Baris Anak Cocok   : {$matchedChildren} anak".PHP_EOL;
    echo " - Total Records Pengukuran : {$importedCount} data bulanan dimasukkan/diperbarui".PHP_EOL;
    echo ' - Total Anak Dilewati      : '.count($skippedChildren).' anak'.PHP_EOL;
    echo '=========================================================='.PHP_EOL;

    if (! empty($skippedChildren)) {
        echo PHP_EOL.'DAFTAR BARIS DILEWATI (Jika Ada):'.PHP_EOL;
        foreach ($skippedChildren as $skip) {
            echo '  '.$skip.PHP_EOL;
        }
    }

} catch (Throwable $e) {
    DB::rollBack();
    echo 'ERROR (ROLLBACK): '.$e->getMessage().PHP_EOL;
    exit(1);
}
