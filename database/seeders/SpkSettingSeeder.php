<?php

namespace Database\Seeders;

use App\Models\SpkCriteria;
use App\Models\SpkSetting;
use Illuminate\Database\Seeder;

class SpkSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaultCriterias = [
            [
                'kode' => 'C1',
                'nama' => 'TB/U Z-Score (Standar WHO)',
                'bobot' => 0.4500,
                'jenis' => 'cost',
                'tipe_sumber' => 'otomatis_tbu',
                'keterangan' => 'Indikator tinggi badan per umur standar WHO untuk mendeteksi stunting',
                'urutan' => 1,
                'is_active' => true,
            ],
            [
                'kode' => 'C2',
                'nama' => 'Growth Faltering (KMS)',
                'bobot' => 0.2500,
                'jenis' => 'cost',
                'tipe_sumber' => 'otomatis_growth_faltering',
                'keterangan' => 'Tren kenaikan berat badan balita bulanan vs standar KBM KMS',
                'urutan' => 2,
                'is_active' => true,
            ],
            [
                'kode' => 'C3',
                'nama' => 'BB/U Z-Score (Standar WHO)',
                'bobot' => 0.2000,
                'jenis' => 'cost',
                'tipe_sumber' => 'otomatis_bbu',
                'keterangan' => 'Indikator berat badan per umur standar WHO untuk mendeteksi status gizi',
                'urutan' => 3,
                'is_active' => true,
            ],
            [
                'kode' => 'C4',
                'nama' => 'Riwayat BBLR',
                'bobot' => 0.1000,
                'jenis' => 'cost',
                'tipe_sumber' => 'otomatis_bblr',
                'keterangan' => 'Riwayat berat lahir rendah (< 2500 gram) sebagai faktor risiko stunting',
                'urutan' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($defaultCriterias as $criteria) {
            SpkCriteria::updateOrCreate(
                ['kode' => $criteria['kode']],
                $criteria
            );
        }

        SpkSetting::updateOrCreate(
            ['key' => 'threshold_tinggi'],
            ['value' => '0.6374', 'deskripsi' => 'Batas ambang atas Nilai V untuk kategori Risiko Tinggi (V < threshold_tinggi)']
        );

        SpkSetting::updateOrCreate(
            ['key' => 'threshold_sedang'],
            ['value' => '0.7857', 'deskripsi' => 'Batas ambang atas Nilai V untuk kategori Risiko Sedang (threshold_tinggi <= V < threshold_sedang)']
        );
    }
}
