<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use App\Models\User;
use App\Services\SawCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodPengukuranSawTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_period_based_measurement_input_and_upsert_logic()
    {
        $kader = User::where('role', 'kader')->first();
        $anak = Anak::where('posyandu_id', $kader->posyandu_id)->first();

        $this->actingAs($kader);

        // 1. Input baru untuk periode Agustus 2026 (Bulan 8, Tahun 2026)
        $response1 = $this->post(route('pengukuran.store'), [
            'anak_id' => $anak->id,
            'bulan_ukur' => 8,
            'tahun_ukur' => 2026,
            'tinggi_cm' => 75.0,
            'berat_kg' => 8.5,
        ]);

        $response1->assertRedirect();

        $pengukuranCount1 = Pengukuran::where('anak_id', $anak->id)
            ->where('bulan_ukur', 8)
            ->where('tahun_ukur', 2026)
            ->count();

        $this->assertEquals(1, $pengukuranCount1);

        $pengukuran1 = Pengukuran::where('anak_id', $anak->id)
            ->where('bulan_ukur', 8)
            ->where('tahun_ukur', 2026)
            ->first();

        $this->assertEquals(75.0, $pengukuran1->tinggi_cm);
        $this->assertEquals(8.5, $pengukuran1->berat_kg);

        // 2. TIMPA DATA / UPSERT untuk periode Agustus 2026 dengan nilai baru
        $response2 = $this->post(route('pengukuran.store'), [
            'anak_id' => $anak->id,
            'bulan_ukur' => 8,
            'tahun_ukur' => 2026,
            'tinggi_cm' => 76.5,
            'berat_kg' => 9.0,
        ]);

        $response2->assertRedirect();

        // Jumlah baris TIDAK bertambah (tetap 1)
        $pengukuranCount2 = Pengukuran::where('anak_id', $anak->id)
            ->where('bulan_ukur', 8)
            ->where('tahun_ukur', 2026)
            ->count();

        $this->assertEquals(1, $pengukuranCount2);

        $pengukuranUpdated = Pengukuran::where('anak_id', $anak->id)
            ->where('bulan_ukur', 8)
            ->where('tahun_ukur', 2026)
            ->first();

        $this->assertEquals(76.5, $pengukuranUpdated->tinggi_cm);
        $this->assertEquals(9.0, $pengukuranUpdated->berat_kg);
    }

    public function test_ajax_modal_input_returns_json_response()
    {
        $kader = User::where('role', 'kader')->first();
        $anak = Anak::where('posyandu_id', $kader->posyandu_id)->first();

        $this->actingAs($kader);

        $response = $this->postJson(route('pengukuran.store'), [
            'anak_id' => $anak->id,
            'bulan_ukur' => 7,
            'tahun_ukur' => 2026,
            'tinggi_cm' => 74.0,
            'berat_kg' => 8.2,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pengukuran' => [
                    'anak_id' => $anak->id,
                    'tinggi_cm' => 74.0,
                    'berat_kg' => 8.2,
                    'bulan_ukur' => 7,
                    'tahun_ukur' => 2026,
                ],
            ]);
    }

    public function test_saw_ranking_calculates_and_filters_per_period()
    {
        $sawService = app(SawCalculatorService::class);
        $posyandu = Posyandu::first();

        // Hitung SAW untuk periode Juli 2026 (Bulan 7, 2026)
        $resultsJuli = $sawService->hitungUntukPosyanduPeriode($posyandu->id, 7, 2026);
        $this->assertNotEmpty($resultsJuli);

        foreach ($resultsJuli as $item) {
            $this->assertEquals(7, $item->bulan_ukur);
            $this->assertEquals(2026, $item->tahun_ukur);
        }

        // Halaman ranking SAW dengan filter periode
        $kader = User::where('role', 'kader')->first();
        $this->actingAs($kader);

        $response = $this->get(route('ranking.index', ['bulan' => 7, 'tahun' => 2026]));
        $response->assertStatus(200);
        $response->assertSee('Periode Aktif');
    }
}
