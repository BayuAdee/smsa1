<?php

namespace Tests\Feature;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaderSearchAndValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_bidan_can_search_kader_via_ajax()
    {
        $bidan = User::where('role', 'bidan')->first();
        $posyandu = Posyandu::first();

        User::create([
            'name' => 'Kader Ani Wahyuni',
            'username' => 'kaderani99',
            'email' => 'ani@posyandu.test',
            'password' => bcrypt('password'),
            'role' => 'kader',
            'posyandu_id' => $posyandu->id,
        ]);

        $this->actingAs($bidan);

        $response = $this->getJson(route('kader.search', ['q' => 'kaderani']));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Kader Ani Wahyuni', $response->json('data.0.name'));
    }

    public function test_kader_creation_fails_when_username_is_duplicate()
    {
        $bidan = User::where('role', 'bidan')->first();
        $posyandu = Posyandu::first();

        User::create([
            'name' => 'Kader Pertama',
            'username' => 'kaderduplikat',
            'email' => 'kader1@test.com',
            'password' => bcrypt('password'),
            'role' => 'kader',
            'posyandu_id' => $posyandu->id,
        ]);

        $this->actingAs($bidan);

        $response = $this->post(route('kader.store'), [
            'name' => 'Kader Kedua',
            'username' => 'kaderduplikat',
            'email' => 'kader2@test.com',
            'password' => 'password123',
            'posyandu_id' => $posyandu->id,
        ]);

        $response->assertSessionHasErrors(['username' => 'Username ini sudah terdaftar untuk pengguna lain.']);
    }

    public function test_kader_creation_fails_when_email_is_duplicate()
    {
        $bidan = User::where('role', 'bidan')->first();
        $posyandu = Posyandu::first();

        User::create([
            'name' => 'Kader Email Satu',
            'username' => 'kaderemail1',
            'email' => 'sama@posyandu.test',
            'password' => bcrypt('password'),
            'role' => 'kader',
            'posyandu_id' => $posyandu->id,
        ]);

        $this->actingAs($bidan);

        $response = $this->post(route('kader.store'), [
            'name' => 'Kader Email Dua',
            'username' => 'kaderemail2',
            'email' => 'sama@posyandu.test',
            'password' => 'password123',
            'posyandu_id' => $posyandu->id,
        ]);

        $response->assertSessionHasErrors(['email' => 'Email ini sudah terdaftar untuk pengguna lain.']);
    }
}
