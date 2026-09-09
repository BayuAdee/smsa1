<?php

namespace Database\Seeders;

use App\Models\Anak;
use App\Models\KbmReference;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use App\Models\User;
use App\Models\ZscoreReference;
use App\Services\SawCalculatorService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed KBM References from CSV
        $kbmPath = database_path('seeders/data/kbm_references.csv');
        if (file_exists($kbmPath)) {
            $file = fopen($kbmPath, 'r');
            $header = fgetcsv($file);
            while (($row = fgetcsv($file)) !== false) {
                KbmReference::updateOrCreate(
                    ['usia_bulan' => (int) $row[0]],
                    ['kbm_gram' => (int) $row[1]]
                );
            }
            fclose($file);
        }

        // 2. Seed ZScore References from CSV
        $zscorePath = database_path('seeders/data/zscore_references.csv');
        if (file_exists($zscorePath)) {
            $file = fopen($zscorePath, 'r');
            $header = fgetcsv($file);
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
            }
            fclose($file);
        }

        // 3. Seed Posyandus
        $posyandu1 = Posyandu::create(['nama' => 'Posyandu Melati 01', 'wilayah' => 'Kelurahan Sukamaju RW 01']);
        $posyandu2 = Posyandu::create(['nama' => 'Posyandu Mawar 02', 'wilayah' => 'Kelurahan Sukamaju RW 02']);
        $posyandu3 = Posyandu::create(['nama' => 'Posyandu Anggrek 03', 'wilayah' => 'Kelurahan Sukamaju RW 03']);

        // 4. Seed Users (Bidan & Kader)
        $bidan = User::create([
            'posyandu_id' => null,
            'name' => 'Bidan Siti Aminah, S.Tr.Keb',
            'username' => 'bidan',
            'email' => 'bidan@posyandu.id',
            'password' => Hash::make('password'),
            'role' => 'bidan',
        ]);

        User::create([
            'posyandu_id' => null,
            'name' => 'Ny. Tonik Purwanto, A.Md.Keb',
            'username' => 'ny_tonik',
            'email' => 'tonik@posyandu.id',
            'password' => Hash::make('bidan123'),
            'role' => 'bidan',
        ]);

        User::create([
            'posyandu_id' => null,
            'name' => 'Bidan Desa Bentak',
            'username' => 'bidan_desa',
            'email' => 'bidan_desa@posyandu.id',
            'password' => Hash::make('password123'),
            'role' => 'bidan',
        ]);

        User::create([
            'posyandu_id' => null,
            'name' => 'Bidan 1 Desa Bentak',
            'username' => 'bidan_bentak',
            'email' => 'bentak@posyandu.id',
            'password' => Hash::make('desabentak123'),
            'role' => 'bidan',
        ]);

        $kader1 = User::create([
            'posyandu_id' => $posyandu1->id,
            'name' => 'Kader Anisa Rahmawati',
            'username' => 'kader1',
            'email' => 'kader1@posyandu.id',
            'password' => Hash::make('password'),
            'role' => 'kader',
        ]);

        $kader2 = User::create([
            'posyandu_id' => $posyandu2->id,
            'name' => 'Kader Budi Santoso',
            'username' => 'kader2',
            'email' => 'kader2@posyandu.id',
            'password' => Hash::make('password'),
            'role' => 'kader',
        ]);

        // 5. Seed Anak (Balita)
        $dummyAnaks = [
            // Posyandu 1
            [
                'posyandu_id' => $posyandu1->id,
                'nama' => 'Ahmad Raihan',
                'nik' => '3201011201230001',
                'tanggal_lahir' => now()->subMonths(18)->format('Y-m-d'),
                'jenis_kelamin' => 'L',
                'berat_lahir_gram' => 2400,
                'status_bblr' => 'bblr',
                'token_akses' => Anak::generateTokenAkses(),
                'nama_orang_tua' => 'Ibu Fatimah',
                'measurements' => [
                    ['bulan_lalu' => 2, 'tb' => 74.0, 'bb' => 8.2],
                    ['bulan_lalu' => 1, 'tb' => 74.8, 'bb' => 8.3],
                    ['bulan_lalu' => 0, 'tb' => 75.2, 'bb' => 8.4],
                ],
            ],
            [
                'posyandu_id' => $posyandu1->id,
                'nama' => 'Siti Aisyah',
                'nik' => '3201011502230002',
                'tanggal_lahir' => now()->subMonths(24)->format('Y-m-d'),
                'jenis_kelamin' => 'P',
                'berat_lahir_gram' => 3100,
                'status_bblr' => 'tidak',
                'token_akses' => Anak::generateTokenAkses(),
                'nama_orang_tua' => 'Ibu Maryam',
                'measurements' => [
                    ['bulan_lalu' => 2, 'tb' => 84.5, 'bb' => 11.5],
                    ['bulan_lalu' => 1, 'tb' => 85.2, 'bb' => 11.8],
                    ['bulan_lalu' => 0, 'tb' => 86.0, 'bb' => 12.1],
                ],
            ],
            [
                'posyandu_id' => $posyandu1->id,
                'nama' => 'Muhammad Bilal',
                'nik' => '3201012003230003',
                'tanggal_lahir' => now()->subMonths(12)->format('Y-m-d'),
                'jenis_kelamin' => 'L',
                'berat_lahir_gram' => 2200,
                'status_bblr' => 'bblr',
                'token_akses' => Anak::generateTokenAkses(),
                'nama_orang_tua' => 'Ibu Nurul',
                'measurements' => [
                    ['bulan_lalu' => 2, 'tb' => 69.5, 'bb' => 7.1],
                    ['bulan_lalu' => 1, 'tb' => 69.8, 'bb' => 7.1], // Faltering
                    ['bulan_lalu' => 0, 'tb' => 70.0, 'bb' => 7.0], // Weight loss
                ],
            ],
            [
                'posyandu_id' => $posyandu1->id,
                'nama' => 'Khadijah Zahra',
                'nik' => '3201011005230004',
                'tanggal_lahir' => now()->subMonths(8)->format('Y-m-d'),
                'jenis_kelamin' => 'P',
                'berat_lahir_gram' => 2900,
                'status_bblr' => 'tidak',
                'token_akses' => Anak::generateTokenAkses(),
                'nama_orang_tua' => 'Ibu Zulaikha',
                'measurements' => [
                    ['bulan_lalu' => 1, 'tb' => 65.0, 'bb' => 7.4],
                    ['bulan_lalu' => 0, 'tb' => 66.5, 'bb' => 7.8],
                ],
            ],
            // Posyandu 2
            [
                'posyandu_id' => $posyandu2->id,
                'nama' => 'Daffa Ibnu',
                'nik' => '3201020406230005',
                'tanggal_lahir' => now()->subMonths(15)->format('Y-m-d'),
                'jenis_kelamin' => 'L',
                'berat_lahir_gram' => 3200,
                'status_bblr' => 'tidak',
                'token_akses' => Anak::generateTokenAkses(),
                'nama_orang_tua' => 'Ibu Ratna',
                'measurements' => [
                    ['bulan_lalu' => 2, 'tb' => 77.0, 'bb' => 9.5],
                    ['bulan_lalu' => 1, 'tb' => 78.0, 'bb' => 9.8],
                    ['bulan_lalu' => 0, 'tb' => 79.0, 'bb' => 10.1],
                ],
            ],
            [
                'posyandu_id' => $posyandu2->id,
                'nama' => 'Nabila Putri',
                'nik' => '3201021208230006',
                'tanggal_lahir' => now()->subMonths(20)->format('Y-m-d'),
                'jenis_kelamin' => 'P',
                'berat_lahir_gram' => 2350,
                'status_bblr' => 'bblr',
                'token_akses' => Anak::generateTokenAkses(),
                'nama_orang_tua' => 'Ibu Dewi',
                'measurements' => [
                    ['bulan_lalu' => 2, 'tb' => 78.5, 'bb' => 8.8],
                    ['bulan_lalu' => 1, 'tb' => 78.8, 'bb' => 8.9],
                    ['bulan_lalu' => 0, 'tb' => 79.2, 'bb' => 8.9], // Faltering
                ],
            ],
        ];

        foreach ($dummyAnaks as $data) {
            $measurements = $data['measurements'];
            unset($data['measurements']);

            $anak = Anak::create($data);

            foreach ($measurements as $m) {
                $tgl = now()->subMonths($m['bulan_lalu'])->startOfMonth()->addDays(5)->format('Y-m-d');
                $usia = (int) floor(Carbon::parse($anak->tanggal_lahir)->diffInMonths(Carbon::parse($tgl)));

                Pengukuran::create([
                    'anak_id' => $anak->id,
                    'tanggal_ukur' => $tgl,
                    'usia_bulan' => $usia,
                    'tinggi_cm' => $m['tb'],
                    'berat_kg' => $m['bb'],
                    'dibuat_oleh' => $anak->posyandu_id == $posyandu1->id ? $kader1->id : $kader2->id,
                ]);
            }
        }

        // 6. Trigger SPK SAW Calculation for seeded measurements
        $sawCalc = app(SawCalculatorService::class);
        $sawCalc->calculateForPosyandu($posyandu1->id);
        $sawCalc->calculateForPosyandu($posyandu2->id);
    }
}
