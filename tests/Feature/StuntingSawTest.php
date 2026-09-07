<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Posyandu;
use App\Models\User;
use App\Services\SawCalculatorService;
use App\Services\ZscoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
        $raihan = Anak::withoutGlobalScope('posyandu_scope')->where('nama', 'like', '%Raihan%')->firstOrFail();
        $response = $this->get('/ortu/'.$raihan->token_akses);
        $response->assertStatus(200);
        $response->assertSee('Ahmad Raihan');
    }

    public function test_public_ortu_can_access_daffa_token_and_see_measurements()
    {
        $daffa = Anak::withoutGlobalScope('posyandu_scope')->where('nama', 'like', '%Daffa%')->firstOrFail();
        $response = $this->get('/ortu/'.$daffa->token_akses);
        $response->assertStatus(200);
        $response->assertSee('Daffa Ibnu');
        $response->assertSee('79'); // tinggi cm
    }

    public function test_ortu_portal_works_even_when_kader_from_another_posyandu_is_logged_in()
    {
        $kader1 = User::where('username', 'kader1')->first();
        $this->actingAs($kader1);

        $daffa = Anak::withoutGlobalScope('posyandu_scope')->where('nama', 'like', '%Daffa%')->firstOrFail();

        // Daffa is in Posyandu 2 while Kader 1 is in Posyandu 1
        $response = $this->get('/ortu/'.$daffa->token_akses);
        $response->assertStatus(200);
        $response->assertSee('Daffa Ibnu');
        $response->assertSee('79');
    }
}
