<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Posyandu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class KaderCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $bidan;
    protected $kader;
    protected $posyandu;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->posyandu = Posyandu::first();

        $this->bidan = User::where('role', 'bidan')->first();
        $this->kader = User::where('role', 'kader')->first();
    }

    public function test_bidan_can_view_kader_index()
    {
        $response = $this->actingAs($this->bidan)->get(route('kader.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Petugas Kader Posyandu');
        $response->assertSee($this->kader->name);
    }

    public function test_bidan_can_create_kader()
    {
        $data = [
            'name' => 'Kader Baru Test',
            'username' => 'kaderbarutest',
            'email' => 'kaderbarutest@posyandu.id',
            'password' => 'password123',
            'posyandu_id' => $this->posyandu->id,
        ];

        $response = $this->actingAs($this->bidan)->post(route('kader.store'), $data);

        $response->assertRedirect(route('kader.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'kaderbarutest',
            'role' => 'kader',
            'posyandu_id' => $this->posyandu->id,
        ]);
    }

    public function test_bidan_can_update_kader()
    {
        $kaderTarget = User::create([
            'name' => 'Kader Update Target',
            'username' => 'kaderupdatetarget',
            'email' => 'updatetarget@posyandu.id',
            'password' => Hash::make('password'),
            'role' => 'kader',
            'posyandu_id' => $this->posyandu->id,
        ]);

        $updateData = [
            'name' => 'Kader Updated Name',
            'username' => 'kaderupdatetarget',
            'email' => 'updatedemail@posyandu.id',
            'password' => '', // keep old password
            'posyandu_id' => $this->posyandu->id,
        ];

        $response = $this->actingAs($this->bidan)->put(route('kader.update', $kaderTarget->id), $updateData);

        $response->assertRedirect(route('kader.index'));
        $this->assertDatabaseHas('users', [
            'id' => $kaderTarget->id,
            'name' => 'Kader Updated Name',
            'email' => 'updatedemail@posyandu.id',
        ]);
    }

    public function test_bidan_can_delete_kader()
    {
        $kaderTarget = User::create([
            'name' => 'Kader Delete Target',
            'username' => 'kaderdeletetarget',
            'email' => 'deletetarget@posyandu.id',
            'password' => Hash::make('password'),
            'role' => 'kader',
            'posyandu_id' => $this->posyandu->id,
        ]);

        $response = $this->actingAs($this->bidan)->delete(route('kader.destroy', $kaderTarget->id));

        $response->assertRedirect(route('kader.index'));
        $this->assertDatabaseMissing('users', [
            'id' => $kaderTarget->id,
        ]);
    }

    public function test_kader_cannot_access_kader_management()
    {
        $response = $this->actingAs($this->kader)->get(route('kader.index'));
        $response->assertStatus(403);

        $createResponse = $this->actingAs($this->kader)->get(route('kader.create'));
        $createResponse->assertStatus(403);
    }
}
