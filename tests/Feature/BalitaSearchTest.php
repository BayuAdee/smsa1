<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalitaSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $bidan;

    protected User $kaderPosyanduA;

    protected Posyandu $posyanduA;

    protected Posyandu $posyanduB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->posyanduA = Posyandu::create(['nama' => 'Posyandu Mawar', 'wilayah' => 'RW 01', 'is_active' => true]);
        $this->posyanduB = Posyandu::create(['nama' => 'Posyandu Melati', 'wilayah' => 'RW 02', 'is_active' => true]);

        $this->bidan = User::create([
            'name' => 'Bidan Desa',
            'username' => 'bidan_test',
            'email' => 'bidan@test.com',
            'password' => bcrypt('password'),
            'role' => 'bidan',
        ]);

        $this->kaderPosyanduA = User::create([
            'name' => 'Kader Mawar',
            'username' => 'kader_mawar',
            'email' => 'kader@mawar.com',
            'password' => bcrypt('password'),
            'role' => 'kader',
            'posyandu_id' => $this->posyanduA->id,
        ]);
    }

    public function test_search_requires_minimum_3_characters()
    {
        Anak::create([
            'posyandu_id' => $this->posyanduA->id,
            'nama' => 'Budi Santoso',
            'nik' => '3201010101010001',
            'tanggal_lahir' => '2024-01-01',
            'jenis_kelamin' => 'L',
            'berat_lahir_gram' => 3000,
            'status_bblr' => 'tidak',
            'token_akses' => Anak::generateTokenAkses(),
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->bidan)->getJson(route('balita.search', ['q' => 'Bu']));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [],
            'message' => 'Kata kunci minimal 3 karakter.',
        ]);
    }

    public function test_search_returns_matching_children()
    {
        $anak1 = Anak::create([
            'posyandu_id' => $this->posyanduA->id,
            'nama' => 'Ahmad Raihan',
            'nik' => '3201010101010002',
            'tanggal_lahir' => '2024-02-01',
            'jenis_kelamin' => 'L',
            'berat_lahir_gram' => 3200,
            'status_bblr' => 'tidak',
            'nama_orang_tua' => 'Siti Khadijah',
            'token_akses' => Anak::generateTokenAkses(),
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->bidan)->getJson(route('balita.search', ['q' => 'Raihan']));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.nama', 'Ahmad Raihan');
        $response->assertJsonPath('data.0.posyandu_nama', 'Posyandu Mawar');
    }

    public function test_kader_search_is_scoped_to_own_posyandu()
    {
        // Child in Posyandu A
        Anak::create([
            'posyandu_id' => $this->posyanduA->id,
            'nama' => 'Clarissa Mawar',
            'nik' => '3201010101010003',
            'tanggal_lahir' => '2024-03-01',
            'jenis_kelamin' => 'P',
            'berat_lahir_gram' => 2900,
            'status_bblr' => 'tidak',
            'token_akses' => Anak::generateTokenAkses(),
            'status_aktif' => true,
        ]);

        // Child in Posyandu B
        Anak::create([
            'posyandu_id' => $this->posyanduB->id,
            'nama' => 'Clarissa Melati',
            'nik' => '3201010101010004',
            'tanggal_lahir' => '2024-03-01',
            'jenis_kelamin' => 'P',
            'berat_lahir_gram' => 3100,
            'status_bblr' => 'tidak',
            'token_akses' => Anak::generateTokenAkses(),
            'status_aktif' => true,
        ]);

        // Kader A searches "Clarissa"
        $response = $this->actingAs($this->kaderPosyanduA)->getJson(route('balita.search', ['q' => 'Clarissa']));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.nama', 'Clarissa Mawar');
    }
}
