<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $bidan;

    protected User $kader;

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

        $this->kader = User::create([
            'name' => 'Kader Mawar',
            'username' => 'kader_test',
            'email' => 'kader@test.com',
            'password' => bcrypt('password'),
            'role' => 'kader',
            'posyandu_id' => $this->posyanduA->id,
        ]);
    }

    public function test_bidan_can_access_import_export_page()
    {
        $response = $this->actingAs($this->bidan)->get(route('import-export.index'));

        $response->assertStatus(200);
        $response->assertSee('Import &amp; Export Data', false);
        $response->assertSee('📥 Import Data Balita');
    }

    public function test_kader_can_access_import_export_page_export_only()
    {
        $response = $this->actingAs($this->kader)->get(route('import-export.index'));

        $response->assertStatus(200);
        $response->assertSee('Form Filter Export Laporan');
        // Import tab button is hidden for Kader
        $response->assertDontSee('📥 Import Data Balita');
    }

    public function test_bidan_can_download_import_template()
    {
        $response = $this->actingAs($this->bidan)->get(route('import-export.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_kader_forbidden_from_downloading_import_template()
    {
        $response = $this->actingAs($this->kader)->get(route('import-export.template'));

        $response->assertStatus(403);
    }

    public function test_bidan_can_preview_and_validate_import_csv()
    {
        $csvContent = "nama,nik,tanggal_lahir,jenis_kelamin,berat_lahir_gram,status_bblr,nama_orang_tua,posyandu\n".
            "Budi Santoso,3201011234567890,12-05-2025,L,3400,tidak,Siti Aminah,Posyandu Mawar\n".
            "Balita Error,3201010101010002,12-05-2025,X,200,salah,Ortu Error,Posyandu Tidak Ada\n";

        $file = UploadedFile::fake()->createWithContent('import.csv', $csvContent);

        $response = $this->actingAs($this->bidan)->post(route('import-export.preview'), [
            'file_import' => $file,
        ]);

        $response->assertRedirect(route('import-export.index', ['tab' => 'import']));
        $response->assertSessionHas('import_preview_data');

        $preview = session('import_preview_data');
        $this->assertEquals(2, $preview['total']);
        $this->assertEquals(1, $preview['valid_count']);
        $this->assertEquals(1, $preview['error_count']);
    }

    public function test_kader_forbidden_from_previewing_import()
    {
        $file = UploadedFile::fake()->createWithContent('import.csv', "posyandu_id,nama\n1,Test");

        $response = $this->actingAs($this->kader)->post(route('import-export.preview'), [
            'file_import' => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_bidan_can_execute_import_for_valid_rows()
    {
        // Setup session preview
        session(['import_preview_data' => [
            'total' => 2,
            'valid_count' => 1,
            'error_count' => 1,
            'rows' => [
                [
                    'row_number' => 2,
                    'is_valid' => true,
                    'errors' => [],
                    'data' => [
                        'posyandu_id' => $this->posyanduA->id,
                        'nama' => 'Anak Import Valid',
                        'nik' => '3201010101010099',
                        'tanggal_lahir' => '2024-02-10',
                        'jenis_kelamin' => 'P',
                        'berat_lahir_gram' => 3000,
                        'status_bblr' => 'tidak',
                        'nama_orang_tua' => 'Ibu Pertiwi',
                    ],
                ],
                [
                    'row_number' => 3,
                    'is_valid' => false,
                    'errors' => ['Posyandu ID tidak ditemukan'],
                    'data' => [],
                ],
            ],
        ]]);

        $response = $this->actingAs($this->bidan)->post(route('import-export.execute'));

        $response->assertRedirect(route('import-export.index', ['tab' => 'import']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('anaks', [
            'posyandu_id' => $this->posyanduA->id,
            'nama' => 'Anak Import Valid',
            'nik' => '3201010101010099',
        ]);
    }

    public function test_kader_forbidden_from_executing_import()
    {
        $response = $this->actingAs($this->kader)->post(route('import-export.execute'));

        $response->assertStatus(403);
    }

    public function test_bidan_and_kader_can_export_reports()
    {
        Anak::create([
            'posyandu_id' => $this->posyanduA->id,
            'nama' => 'Anak A',
            'nik' => '3201010101010001',
            'tanggal_lahir' => '2024-01-01',
            'jenis_kelamin' => 'L',
            'berat_lahir_gram' => 3100,
            'status_bblr' => 'tidak',
            'token_akses' => 'BALITA-ANAKA-01',
            'status_aktif' => true,
        ]);

        // Export Excel (CSV Stream)
        $responseExcel = $this->actingAs($this->bidan)->get(route('import-export.export', [
            'posyandu_id' => $this->posyanduA->id,
            'bulan' => 9,
            'tahun' => 2026,
            'jenis_data' => 'profil_balita',
            'format' => 'excel',
        ]));

        $responseExcel->assertStatus(200);
        $responseExcel->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Export PDF
        $responsePdf = $this->actingAs($this->kader)->get(route('import-export.export', [
            'posyandu_id' => $this->posyanduB->id, // Kader should be auto forced to posyanduA
            'bulan' => 9,
            'tahun' => 2026,
            'jenis_data' => 'profil_balita',
            'format' => 'pdf',
        ]));

        $responsePdf->assertStatus(200);
        $responsePdf->assertSee('Mode Pratinjau Cetak Laporan PDF');
        $responsePdf->assertSee('Posyandu Mawar'); // Proves Kader export was locked to Posyandu Mawar
    }
}
