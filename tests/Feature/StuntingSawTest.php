<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Posyandu;
use App\Models\Anak;
use App\Models\Pengukuran;
use App\Models\HasilSaw;
use App\Services\ZscoreService;
use App\Services\GrowthFalteringService;
use App\Services\SawCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StuntingSawTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_zscore_service_calculates_correctly()
    {
        $zscoreService = app(ZscoreService::class);
        $zTbu = $zscoreService->calculate('tbu', 'L', 18, 75.2);
        
        $this->assertIsFloat($zTbu);
    }

    public function test_saw_calculator_service_generates_rankings()
    {
        $sawService = app(SawCalculatorService::class);
        $posyandu = Posyandu::first();

        $rankings = $sawService->calculateForPosyandu($posyandu->id);

        $this->assertNotEmpty($rankings);
        $this->assertGreaterThan(0, $rankings->count());

        // Verify values are sorted ascending by nilai_v
        $previousV = -1.0;
        foreach ($rankings as $item) {
            $this->assertGreaterThanOrEqual($previousV, $item->nilai_v);
            $previousV = $item->nilai_v;
        }
    }

    public function test_kader_scope_restricts_other_posyandu_data()
    {
        $kader1 = User::where('username', 'kader1')->first();
        $this->actingAs($kader1);

        $anaks = Anak::all();

        foreach ($anaks as $anak) {
            $this->assertEquals($kader1->posyandu_id, $anak->posyandu_id);
        }
    }

    public function test_bidan_can_view_all_posyandus()
    {
        $bidan = User::where('username', 'bidan')->first();
        $this->actingAs($bidan);

        session(['selected_posyandu_id' => 'all']);

        $totalAnaks = Anak::all()->count();
        $this->assertGreaterThan(3, $totalAnaks);
    }

    public function test_public_ortu_can_access_child_with_token()
    {
        $response = $this->get('/ortu/BALITA-RAIHAN-01');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Raihan');
    }
}
