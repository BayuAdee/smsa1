<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\HasilSaw;
use App\Models\Pengukuran;
use App\Models\SpkCriteria;
use App\Models\SpkSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpkParameterSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_bidan_can_view_spk_settings_page()
    {
        $bidan = User::where('role', 'bidan')->first();
        $this->actingAs($bidan);

        $response = $this->get(route('settings.spk.index'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Kriteria');
        $response->assertSee('TB/U Z-Score');
        $response->assertSee('Growth Faltering');
        $response->assertSee('Riwayat BBLR');
    }

    public function test_kader_cannot_access_spk_settings_page()
    {
        $kader = User::where('role', 'kader')->first();
        $this->actingAs($kader);

        $response = $this->get(route('settings.spk.index'));
        $response->assertStatus(403);
    }

    public function test_bidan_can_update_criteria_and_thresholds()
    {
        $bidan = User::where('role', 'bidan')->first();
        $this->actingAs($bidan);

        $c1 = SpkCriteria::where('kode', 'C1')->first();
        $c2 = SpkCriteria::where('kode', 'C2')->first();
        $c3 = SpkCriteria::where('kode', 'C3')->first();
        $c4 = SpkCriteria::where('kode', 'C4')->first();

        $payload = [
            'criterias' => [
                [
                    'id' => $c1->id,
                    'nama' => 'Indikator Tinggi Badan (TB/U)',
                    'bobot' => 40.0,
                    'jenis' => 'cost',
                    'tipe_sumber' => 'otomatis_tbu',
                ],
                [
                    'id' => $c2->id,
                    'nama' => 'Kenaikan Bobot KMS',
                    'bobot' => 30.0,
                    'jenis' => 'cost',
                    'tipe_sumber' => 'otomatis_growth_faltering',
                ],
                [
                    'id' => $c3->id,
                    'nama' => 'Indikator Berat Badan (BB/U)',
                    'bobot' => 20.0,
                    'jenis' => 'cost',
                    'tipe_sumber' => 'otomatis_bbu',
                ],
                [
                    'id' => $c4->id,
                    'nama' => 'Variabel Baru Sanitasi Lingkungan',
                    'bobot' => 10.0,
                    'jenis' => 'cost',
                    'tipe_sumber' => 'kustom',
                ],
            ],
            'threshold_tinggi' => '0.6000',
            'threshold_sedang' => '0.8000',
            'recalculate_now' => '1',
        ];

        $response = $this->put(route('settings.spk.update'), $payload);
        $response->assertRedirect(route('settings.spk.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('spk_criterias', [
            'kode' => 'C4',
            'nama' => 'Variabel Baru Sanitasi Lingkungan',
            'bobot' => 0.1000,
            'tipe_sumber' => 'kustom',
        ]);

        $this->assertEquals('0.6000', SpkSetting::get('threshold_tinggi'));
        $this->assertEquals('0.8000', SpkSetting::get('threshold_sedang'));
    }

    public function test_validation_fails_if_total_weight_is_not_100_percent()
    {
        $bidan = User::where('role', 'bidan')->first();
        $this->actingAs($bidan);

        $criterias = SpkCriteria::orderBy('urutan')->get();

        $payload = [
            'criterias' => [
                ['id' => $criterias[0]->id, 'nama' => 'C1', 'bobot' => 50.0, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_tbu'],
                ['id' => $criterias[1]->id, 'nama' => 'C2', 'bobot' => 20.0, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_growth_faltering'],
                ['id' => $criterias[2]->id, 'nama' => 'C3', 'bobot' => 10.0, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_bbu'],
                ['id' => $criterias[3]->id, 'nama' => 'C4', 'bobot' => 10.0, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_bblr'], // Total 90%
            ],
            'threshold_tinggi' => '0.6374',
            'threshold_sedang' => '0.7857',
        ];

        $response = $this->put(route('settings.spk.update'), $payload);
        $response->assertSessionHasErrors(['total_bobot']);
    }

    public function test_validation_fails_if_threshold_tinggi_is_greater_than_sedang()
    {
        $bidan = User::where('role', 'bidan')->first();
        $this->actingAs($bidan);

        $criterias = SpkCriteria::orderBy('urutan')->get();

        $payload = [
            'criterias' => [
                ['id' => $criterias[0]->id, 'nama' => 'C1', 'bobot' => 45.0, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_tbu'],
                ['id' => $criterias[1]->id, 'nama' => 'C2', 'bobot' => 25.0, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_growth_faltering'],
                ['id' => $criterias[2]->id, 'nama' => 'C3', 'bobot' => 20.0, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_bbu'],
                ['id' => $criterias[3]->id, 'nama' => 'C4', 'bobot' => 10.0, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_bblr'],
            ],
            'threshold_tinggi' => '0.8500',
            'threshold_sedang' => '0.7000',
        ];

        $response = $this->put(route('settings.spk.update'), $payload);
        $response->assertSessionHasErrors(['threshold_tinggi']);
    }

    public function test_bidan_can_save_custom_sub_criteria_and_measurements_use_it()
    {
        $bidan = User::where('role', 'bidan')->first();
        $this->actingAs($bidan);

        $c4 = SpkCriteria::where('kode', 'C4')->first();
        $c1 = SpkCriteria::where('kode', 'C1')->first();
        $c2 = SpkCriteria::where('kode', 'C2')->first();
        $c3 = SpkCriteria::where('kode', 'C3')->first();

        // 1. Simpan kriteria C4 sebagai Kustom dengan 3 sub-kriteria
        $payload = [
            'criterias' => [
                ['id' => $c1->id, 'nama' => $c1->nama, 'bobot' => 45.0, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_tbu'],
                ['id' => $c2->id, 'nama' => $c2->nama, 'bobot' => 25.0, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_growth_faltering'],
                ['id' => $c3->id, 'nama' => $c3->nama, 'bobot' => 20.0, 'jenis' => 'cost', 'tipe_sumber' => 'otomatis_bbu'],
                [
                    'id' => $c4->id,
                    'nama' => 'Pemberian ASI Eksklusif',
                    'bobot' => 10.0,
                    'jenis' => 'cost',
                    'tipe_sumber' => 'kustom',
                    'sub_kriteria' => [
                        ['label' => 'ASI Eksklusif 6 Bulan', 'skor' => 1.0],
                        ['label' => 'Campur Susu Formula', 'skor' => 2.0],
                        ['label' => 'Tidak Diberi ASI', 'skor' => 4.0],
                    ],
                ],
            ],
            'threshold_tinggi' => '0.6374',
            'threshold_sedang' => '0.7857',
        ];

        $this->put(route('settings.spk.update'), $payload)->assertRedirect();

        $c4Updated = SpkCriteria::where('kode', 'C4')->first();
        $this->assertCount(3, $c4Updated->sub_kriteria);
        $this->assertEquals('ASI Eksklusif 6 Bulan', $c4Updated->sub_kriteria[0]['label']);

        // 2. Input Pengukuran Balita dengan memilih C4 = 2.0 (Campur Formula)
        $anak = Anak::first();
        $kader = User::where('role', 'kader')->first();
        $this->actingAs($kader);

        $response = $this->postJson(route('pengukuran.store'), [
            'anak_id' => $anak->id,
            'bulan_ukur' => 11,
            'tahun_ukur' => 2026,
            'tinggi_cm' => 80.0,
            'berat_kg' => 10.0,
            'nilai_kustom' => [
                'C4' => '2.0',
            ],
        ]);

        $response->assertStatus(200);

        $pengukuran = Pengukuran::where('anak_id', $anak->id)
            ->where('bulan_ukur', 11)
            ->where('tahun_ukur', 2026)
            ->first();

        $this->assertEquals(['C4' => '2.0'], $pengukuran->nilai_kustom);

        // 3. Pastikan hasil SAW C4 raw bernilai 2.0 dan r_c4 = 1.0/2.0 = 0.5
        $hasilSaw = HasilSaw::where('anak_id', $anak->id)
            ->where('bulan_ukur', 11)
            ->where('tahun_ukur', 2026)
            ->first();

        $this->assertNotNull($hasilSaw);
        $this->assertEquals(2.0, $hasilSaw->raw_c4);
        $this->assertEquals(0.5, $hasilSaw->r_c4);
    }

    public function test_bidan_can_reset_spk_settings_to_default()
    {
        $bidan = User::where('role', 'bidan')->first();
        $this->actingAs($bidan);

        // Ubah C4 jadi nama lain
        SpkCriteria::where('kode', 'C4')->update(['nama' => 'Kustom Nama', 'bobot' => 0.05]);

        $response = $this->post(route('settings.spk.reset'));
        $response->assertRedirect(route('settings.spk.index'));
        $response->assertSessionHas('success');

        $c4 = SpkCriteria::where('kode', 'C4')->first();
        $this->assertEquals('Riwayat BBLR', $c4->nama);
        $this->assertEquals(0.1000, $c4->bobot);
    }
}
