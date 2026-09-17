<?php

namespace Database\Seeders;

use App\Models\Pengukuran;
use App\Models\ZscoreReference;
use App\Services\SawCalculatorService;
use Illuminate\Database\Seeder;

class UpdateZscoreSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed ZScore References from CSV Permenkes 2020
        $zscorePath = database_path('seeders/data/zscore_references.csv');
        if (file_exists($zscorePath)) {
            $file = fopen($zscorePath, 'r');
            $header = fgetcsv($file);
            $count = 0;
            while (($row = fgetcsv($file)) !== false) {
                ZscoreReference::updateOrCreate(
                    [
                        'indicator' => $row[0],
                        'jenis_kelamin' => $row[1],
                        'usia_bulan' => (int) $row[2],
                    ],
                    [
                        'l' => (float) $row[3],
                        'm' => (float) $row[4],
                        's' => (float) $row[5],
                        'sd3neg' => (float) $row[6],
                        'sd2neg' => (float) $row[7],
                        'sd1neg' => (float) $row[8],
                        'sd0' => (float) $row[9],
                        'sd1' => (float) $row[10],
                        'sd2' => (float) $row[11],
                        'sd3' => (float) $row[12],
                    ]
                );
                $count++;
            }
            fclose($file);
            $this->command->info("Berhasil meng-update {$count} data Z-Score reference Permenkes 2020.");
        }

        // 2. Recalculate SAW untuk seluruh periode pengukuran eksisting
        $periodes = Pengukuran::withoutGlobalScope('posyandu_scope')
            ->select('bulan_ukur', 'tahun_ukur')
            ->distinct()
            ->get();

        $sawCalculator = app(SawCalculatorService::class);
        foreach ($periodes as $p) {
            $sawCalculator->hitungUntukPosyanduPeriode(null, (int) $p->bulan_ukur, (int) $p->tahun_ukur);
        }

        $this->command->info('Berhasil menghitung ulang Z-Score & SPK SAW untuk seluruh '.count($periodes).' periode pengukuran.');
    }
}
