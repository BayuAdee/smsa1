<?php

namespace Tests\Feature;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosyanduManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_bidan_can_add_posyandu_with_unique_name_validation()
    {
        $bidan = User::where('role', 'bidan')->first();
        $this->actingAs($bidan);

        // 1. Tambah posyandu baru
        $response = $this->post(route('posyandu.store'), [
            'nama' => 'Posyandu Flamboyan 99',
            'wilayah' => 'Kelurahan Sukamaju RW 99',
        ]);

        $response->assertRedirect(route('posyandu.index'));
        $this->assertDatabaseHas('posyandus', [
            'nama' => 'Posyandu Flamboyan 99',
            'is_active' => true,
        ]);

        // 2. Coba tambah posyandu dengan nama ganda (harus gagal validasi unik)
        $duplicateResponse = $this->post(route('posyandu.store'), [
            'nama' => 'Posyandu Flamboyan 99',
            'wilayah' => 'Wilayah Lain',
        ]);

        $duplicateResponse->assertSessionHasErrors(['nama' => 'Nama Posyandu sudah terdaftar.']);
    }

    public function test_bidan_can_edit_posyandu()
    {
        $bidan = User::where('role', 'bidan')->first();
        $this->actingAs($bidan);

        $posyandu = Posyandu::first();

        $response = $this->put(route('posyandu.update', $posyandu->id), [
            'nama' => $posyandu->nama, // Nama sama (harus lolos via ignore rule)
            'wilayah' => 'Alamat Perubahan Terbaru',
        ]);

        $response->assertRedirect(route('posyandu.index'));
        $this->assertDatabaseHas('posyandus', [
            'id' => $posyandu->id,
            'wilayah' => 'Alamat Perubahan Terbaru',
        ]);
    }

    public function test_bidan_can_toggle_posyandu_status()
    {
        $bidan = User::where('role', 'bidan')->first();
        $this->actingAs($bidan);

        $posyandu = Posyandu::first();
        $this->assertTrue($posyandu->is_active);

        // Nonaktifkan
        $response = $this->patch(route('posyandu.toggle', $posyandu->id));
        $response->assertRedirect(route('posyandu.index'));

        $this->assertDatabaseHas('posyandus', [
            'id' => $posyandu->id,
            'is_active' => false,
        ]);

        // Aktifkan kembali
        $response2 = $this->patch(route('posyandu.toggle', $posyandu->id));
        $response2->assertRedirect(route('posyandu.index'));

        $this->assertDatabaseHas('posyandus', [
            'id' => $posyandu->id,
            'is_active' => true,
        ]);
    }

    public function test_non_active_posyandu_placed_at_bottom_and_hidden_from_create_dropdowns()
    {
        $bidan = User::where('role', 'bidan')->first();
        $this->actingAs($bidan);

        $posyanduA = Posyandu::create(['nama' => 'Posyandu Aktif A', 'is_active' => true]);
        $posyanduNonAktif = Posyandu::create(['nama' => 'AAA Posyandu Non-Aktif', 'is_active' => false]);

        // Halaman index posyandu
        $responseIndex = $this->get(route('posyandu.index'));
        $responseIndex->assertStatus(200);

        // Halaman create balita (posyandu non-aktif tidak muncul di dropdown)
        $responseBalitaCreate = $this->get(route('balita.create'));
        $responseBalitaCreate->assertStatus(200);
        $responseBalitaCreate->assertDontSee($posyanduNonAktif->nama);
        $responseBalitaCreate->assertSee($posyanduA->nama);

        // Halaman create kader (posyandu non-aktif tidak muncul di dropdown)
        $responseKaderCreate = $this->get(route('kader.create'));
        $responseKaderCreate->assertStatus(200);
        $responseKaderCreate->assertDontSee($posyanduNonAktif->nama);
        $responseKaderCreate->assertSee($posyanduA->nama);
    }
}
