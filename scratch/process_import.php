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

echo '=========================================================='.PHP_EOL;
echo " DAFTAR USER UNTUK PENENTUAN 'dibuat_oleh' ".PHP_EOL;
echo '=========================================================='.PHP_EOL;

$users = User::all();
foreach ($users as $u) {
    echo "ID: {$u->id} | Nama: {$u->name} | Role: {$u->role} | Posyandu ID: ".($u->posyandu_id ?? 'Semua/Bidan').PHP_EOL;
}

// Cari user Bidan utama atau Admin sebagai default dibuat_oleh
$defaultUser = User::where('role', 'bidan')->first() ?? User::first();
echo PHP_EOL."User default yang digunakan untuk 'dibuat_oleh': ID {$defaultUser->id} ({$defaultUser->name})".PHP_EOL;

$csvFile = __DIR__.'/test_import.csv';
if (! file_exists($csvFile)) {
    echo 'ERROR: File CSV test_import.csv tidak ditemukan di '.$csvFile.PHP_EOL;
    exit(1);
}

$handle = fopen($csvFile, 'r');
$bom = fread($handle, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($handle);
}

$header = fgetcsv($handle);
if (! $header) {
    echo 'ERROR: Header CSV tidak dapat dibaca.'.PHP_EOL;
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

$year = 2026;
$importedCount = 0;
$affectedPeriods = [];

DB::beginTransaction();

try {
    $rowNum = 1;
    while (($data = fgetcsv($handle)) !== false) {
        $rowNum++;
        if (empty(array_filter($data))) {
            continue;
        }

        $row = array_combine($header, $data);
        $nama = trim($row['Nama'] ?? '');
        $posyanduInput = trim($row['Posyandu'] ?? '');

        if (empty($nama) || empty($posyanduInput)) {
            continue;
        }

        $posyanduObj = Posyandu::where('nama', 'like', "%{$posyanduInput}%")
            ->orWhere('nama', 'like', '%'.str_replace('Posyandu ', '', $posyanduInput).'%')
            ->first();

        if (! $posyanduObj) {
            continue;
        }

        $anak = Anak::withoutGlobalScopes()
            ->where('posyandu_id', $posyanduObj->id)
            ->where('nama', 'like', $nama)
            ->first();

        if (! $anak) {
            $anak = Anak::withoutGlobalScopes()->where('nama', 'like', $nama)->first();
        }

        if (! $anak) {
            continue;
        }

        // Cari user kader khusus posyandu ini jika ada, jika tidak pakai Bidan
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

            $tglUkur = Carbon::createFromDate($year, $bulanAngka, 15);
            $tglLahir = Carbon::parse($anak->tanggal_lahir);
            $usiaBulan = (int) $tglLahir->diffInMonths($tglUkur);

            // Update or Create Pengukuran dengan mengisi 'dibuat_oleh'
            $pengukuran = Pengukuran::withoutGlobalScopes()->updateOrCreate(
                [
                    'anak_id' => $anak->id,
                    'bulan_ukur' => $bulanAngka,
                    'tahun_ukur' => $year,
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
            $affectedPeriods["{$posyanduObj->id}_{$bulanAngka}_{$year}"] = [
                'posyandu_id' => $posyanduObj->id,
                'bulan' => $bulanAngka,
                'tahun' => $year,
            ];
        }
    }

    fclose($handle);
    DB::commit();

    echo PHP_EOL.'=== MENGHITUNG ULANG Z-SCORE & SAW PROPRIETARIS ==='.PHP_EOL;
    $sawService = app(SawCalculatorService::class);
    foreach ($affectedPeriods as $period) {
        $sawService->hitungUntukPosyanduPeriode($period['posyandu_id'], $period['bulan'], $period['tahun']);
    }

    echo PHP_EOL.'=========================================================='.PHP_EOL;
    echo " HASIL REVISI: {$importedCount} data pengukuran diperbarui!".PHP_EOL;
    echo " Kolom 'dibuat_oleh' telah terisi dengan ID User Petugas/Bidan.".PHP_EOL;
    echo '=========================================================='.PHP_EOL;

} catch (Throwable $e) {
    DB::rollBack();
    echo 'ERROR (ROLLBACK): '.$e->getMessage().PHP_EOL;
    exit(1);
}
