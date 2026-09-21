<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NikValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_nik_can_be_null()
    {
        $kader = User::where('role', 'kader')->first();
        $this->actingAs($kader);

        $response = $this->post(route('balita.store'), [
            'nama' => 'Anak Tanpa NIK',
            'nik' => '',
            'tanggal_lahir' => '2024-01-01',
            'jenis_kelamin' => 'L',
            'berat_lahir_gram' => 3000,
            'status_bblr' => 'tidak',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('anaks', [
            'nama' => 'Anak Tanpa NIK',
            'nik' => null,
        ]);
    }

    public function test_nik_fails_if_less_than_16_digits()
    {
        $kader = User::where('role', 'kader')->first();
        $this->actingAs($kader);

        $response = $this->post(route('balita.store'), [
            'nama' => 'Anak NIK Pendek',
            'nik' => '12345',
            'tanggal_lahir' => '2024-01-01',
            'jenis_kelamin' => 'L',
            'berat_lahir_gram' => 3000,
            'status_bblr' => 'tidak',
        ]);

        $response->assertSessionHasErrors(['nik' => 'NIK harus berisi tepat 16 digit angka.']);
    }

    public function test_nik_fails_if_duplicate()
    {
        $kader = User::where('role', 'kader')->first();
        $this->actingAs($kader);

        $nikString = '3201012304200001';

        // 1. Tambah balita pertama dengan NIK tersebut
        $this->post(route('balita.store'), [
            'nama' => 'Balita Pertama',
            'nik' => $nikString,
            'tanggal_lahir' => '2024-01-01',
            'jenis_kelamin' => 'L',
            'berat_lahir_gram' => 3000,
            'status_bblr' => 'tidak',
        ])->assertSessionHasNoErrors();

        // 2. Tambah balita kedua dengan NIK yang sama (harus ditolak)
        $response = $this->post(route('balita.store'), [
            'nama' => 'Balita Kedua',
            'nik' => $nikString,
            'tanggal_lahir' => '2024-02-01',
            'jenis_kelamin' => 'P',
            'berat_lahir_gram' => 3200,
            'status_bblr' => 'tidak',
        ]);

        $response->assertSessionHasErrors(['nik' => 'NIK ini sudah terdaftar untuk balita lain.']);
    }

    public function test_nik_success_when_valid_16_digits()
    {
        $kader = User::where('role', 'kader')->first();
        $this->actingAs($kader);

        $validNik = '3201012304200009';

        $response = $this->post(route('balita.store'), [
            'nama' => 'Anak NIK Valid',
            'nik' => $validNik,
            'tanggal_lahir' => '2024-01-01',
            'jenis_kelamin' => 'L',
            'berat_lahir_gram' => 3000,
            'status_bblr' => 'tidak',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('anaks', [
            'nama' => 'Anak NIK Valid',
            'nik' => $validNik,
        ]);
    }

    public function test_update_balita_allows_same_nik_for_self()
    {
        $kader = User::where('role', 'kader')->first();
        $this->actingAs($kader);

        $validNik = '3201012304200008';

        $anak = Anak::create([
            'posyandu_id' => $kader->posyandu_id,
            'nama' => 'Anak Sebelum Edit',
            'nik' => $validNik,
            'tanggal_lahir' => '2024-01-01',
            'jenis_kelamin' => 'L',
            'berat_lahir_gram' => 3000,
            'status_bblr' => 'tidak',
        ]);

        $response = $this->put(route('balita.update', $anak->id), [
            'nama' => 'Anak Sesudah Edit',
            'nik' => $validNik,
            'tanggal_lahir' => '2024-01-01',
            'jenis_kelamin' => 'L',
            'berat_lahir_gram' => 3100,
            'status_bblr' => 'tidak',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('anaks', [
            'id' => $anak->id,
            'nama' => 'Anak Sesudah Edit',
            'nik' => $validNik,
        ]);
    }
}
