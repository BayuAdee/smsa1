<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\HasilSaw;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ImportExportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isBidan()) {
            $posyandus = Posyandu::where('is_active', true)->orderBy('nama', 'asc')->get();
        } else {
            $posyandus = Posyandu::where('id', $user->posyandu_id)->get();
        }

        $importPreview = session('import_preview_data');
        $activeTab = $request->get('tab', $user->isBidan() ? 'import' : 'export');

        if ($user->isKader()) {
            $activeTab = 'export';
        }

        return view('import_export.index', compact('posyandus', 'importPreview', 'activeTab'));
    }

    public function downloadTemplate()
    {
        Gate::authorize('import-data');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_balita.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'nama',
                'nik',
                'tanggal_lahir',
                'jenis_kelamin',
                'berat_lahir_gram',
                'status_bblr',
                'nama_orang_tua',
                'posyandu',
            ]);

            // Add sample row from user specification
            fputcsv($file, [
                'Budi Santoso',
                '3201011234567890',
                '12-05-2025',
                'L',
                3400,
                'tidak',
                'Siti Aminah',
                'Posyandu Melati',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function previewImport(Request $request)
    {
        Gate::authorize('import-data');

        $request->validate([
            'file_import' => 'required|file|max:2048',
        ], [
            'file_import.required' => 'Pilih file CSV template terlebih dahulu.',
            'file_import.max' => 'Ukuran file maksimal adalah 2MB.',
        ]);

        $file = $request->file('file_import');
        $filePath = $file->getRealPath();

        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Remove BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $delimiter = ',';
            $firstLine = fgets($handle);
            if ($firstLine !== false) {
                if (str_contains($firstLine, ';')) {
                    $delimiter = ';';
                }
                rewind($handle);
                if ($bom === "\xEF\xBB\xBF") {
                    fseek($handle, 3);
                }
            }

            $header = null;
            $rowNumber = 1;

            while (($data = fgetcsv($handle, 2000, $delimiter)) !== false) {
                // Ignore empty rows
                if (empty($data) || (count($data) === 1 && is_null($data[0]))) {
                    continue;
                }

                if (! $header) {
                    $header = array_map('trim', array_map('strtolower', $data));

                    continue;
                }

                $rowNumber++;
                $row = [];
                foreach ($header as $i => $colName) {
                    $row[$colName] = isset($data[$i]) ? trim($data[$i]) : '';
                }

                // Validate row fields
                $errors = [];

                // Posyandu validation (supports Posyandu Name or Posyandu ID)
                $posyanduInput = trim($row['posyandu'] ?? $row['posyandu_id'] ?? '');
                $posyanduObj = null;
                $posyanduId = null;
                $posyanduNama = null;

                if (empty($posyanduInput)) {
                    $errors[] = 'Posyandu wajib diisi.';
                } else {
                    if (is_numeric($posyanduInput)) {
                        $posyanduObj = Posyandu::find((int) $posyanduInput);
                    }
                    if (! $posyanduObj) {
                        $posyanduObj = Posyandu::where('nama', 'like', $posyanduInput)->first();
                    }
                    if (! $posyanduObj) {
                        $posyanduObj = Posyandu::where('nama', 'like', "%{$posyanduInput}%")->first();
                    }

                    if ($posyanduObj) {
                        $posyanduId = $posyanduObj->id;
                        $posyanduNama = $posyanduObj->nama;
                    } else {
                        $errors[] = "Posyandu '{$posyanduInput}' tidak ditemukan di sistem.";
                    }
                }

                // Nama validation
                $nama = $row['nama'] ?? '';
                if (empty($nama)) {
                    $errors[] = 'Nama balita wajib diisi.';
                }

                // NIK validation (optional)
                $nik = $row['nik'] ?? null;
                if (! empty($nik) && strlen($nik) > 20) {
                    $errors[] = 'NIK tidak boleh melebihi 20 karakter.';
                }

                // Tanggal lahir validation (supports DD-MM-YYYY, YYYY-MM-DD, DD/MM/YYYY)
                $tglLahir = trim($row['tanggal_lahir'] ?? '');
                $parsedDate = null;
                if (empty($tglLahir)) {
                    $errors[] = 'Tanggal lahir wajib diisi.';
                } else {
                    try {
                        if (preg_match('/^\d{1,2}[-\/]\d{1,2}[-\/]\d{4}$/', $tglLahir)) {
                            $normalizedDate = str_replace('/', '-', $tglLahir);
                            $parsedDate = Carbon::createFromFormat('d-m-Y', $normalizedDate);
                        } else {
                            $parsedDate = Carbon::parse($tglLahir);
                        }

                        if ($parsedDate->isFuture()) {
                            $errors[] = 'Tanggal lahir tidak boleh di masa depan.';
                        }
                    } catch (\Exception $e) {
                        $errors[] = 'Format tanggal lahir tidak valid (Gunakan DD-MM-YYYY atau YYYY-MM-DD).';
                    }
                }

                // Jenis Kelamin
                $jk = strtoupper(trim($row['jenis_kelamin'] ?? ''));
                if (! in_array($jk, ['L', 'P'])) {
                    $errors[] = 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).';
                }

                // Berat Lahir Gram
                $beratGram = filter_var($row['berat_lahir_gram'] ?? null, FILTER_VALIDATE_INT);
                if ($beratGram === false || $beratGram < 500 || $beratGram > 6000) {
                    $errors[] = 'Berat lahir (gram) harus berupa angka antara 500g - 6000g.';
                }

                // Status BBLR
                $rawBblr = strtolower(trim($row['status_bblr'] ?? ''));
                $statusBblr = 'tidak';
                if (in_array($rawBblr, ['bblr', '1', 'ya', 'true'])) {
                    $statusBblr = 'bblr';
                } elseif (in_array($rawBblr, ['tidak_diketahui', 'unknown', '2'])) {
                    $statusBblr = 'tidak_diketahui';
                } elseif (in_array($rawBblr, ['tidak', '0', 'tidak bblr', 'false', ''])) {
                    $statusBblr = 'tidak';
                } else {
                    $errors[] = 'Status BBLR harus: tidak, bblr, atau tidak_diketahui.';
                }

                // Nama Orang Tua
                $namaOrtu = $row['nama_orang_tua'] ?? '';

                $isValid = empty($errors);

                $rows[] = [
                    'row_number' => $rowNumber,
                    'is_valid' => $isValid,
                    'errors' => $errors,
                    'data' => [
                        'posyandu_id' => $posyanduId,
                        'posyandu_nama' => $posyanduNama,
                        'posyandu_input' => $posyanduInput,
                        'nama' => $nama,
                        'nik' => $nik,
                        'tanggal_lahir' => $parsedDate ? $parsedDate->format('Y-m-d') : $tglLahir,
                        'jenis_kelamin' => $jk,
                        'berat_lahir_gram' => $beratGram,
                        'status_bblr' => $statusBblr,
                        'nama_orang_tua' => $namaOrtu,
                    ],
                ];
            }
            fclose($handle);
        }

        $validCount = count(array_filter($rows, fn ($r) => $r['is_valid']));
        $errorCount = count(array_filter($rows, fn ($r) => ! $r['is_valid']));

        session(['import_preview_data' => [
            'total' => count($rows),
            'valid_count' => $validCount,
            'error_count' => $errorCount,
            'rows' => $rows,
        ]]);

        return redirect()->route('import-export.index', ['tab' => 'import'])
            ->with('success', "File berhasil diunggah. Ditemukan {$validCount} baris valid dan {$errorCount} baris bermasalah.");
    }

    public function executeImport(Request $request)
    {
        Gate::authorize('import-data');

        $preview = session('import_preview_data');

        if (! $preview || empty($preview['rows']) || $preview['valid_count'] === 0) {
            return redirect()->route('import-export.index', ['tab' => 'import'])
                ->withErrors(['import' => 'Tidak ada data valid yang dapat diimpor. Silakan upload file terlebih dahulu.']);
        }

        $imported = 0;
        foreach ($preview['rows'] as $row) {
            if ($row['is_valid']) {
                $d = $row['data'];
                $slugNama = Str::slug(substr($d['nama'], 0, 10));
                $tokenAkses = 'BALITA-'.strtoupper($slugNama).'-'.sprintf('%02d', rand(10, 99));

                Anak::create([
                    'posyandu_id' => $d['posyandu_id'],
                    'nama' => $d['nama'],
                    'nik' => $d['nik'],
                    'tanggal_lahir' => $d['tanggal_lahir'],
                    'jenis_kelamin' => $d['jenis_kelamin'],
                    'berat_lahir_gram' => $d['berat_lahir_gram'],
                    'status_bblr' => $d['status_bblr'],
                    'nama_orang_tua' => $d['nama_orang_tua'],
                    'token_akses' => $tokenAkses,
                    'status_aktif' => true,
                ]);

                $imported++;
            }
        }

        session()->forget('import_preview_data');

        return redirect()->route('import-export.index', ['tab' => 'import'])
            ->with('success', "Proses Import Berhasil! Total {$imported} data balita telah berhasil ditambahkan.");
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020|max:2030',
            'jenis_data' => 'required|in:profil_balita,pengukuran_bulanan,ranking_saw',
            'format' => 'required|in:excel,pdf',
        ]);

        $posyanduId = $request->input('posyandu_id', 'all');

        // Scoped for Kader
        if ($user->isKader()) {
            $posyanduId = $user->posyandu_id;
        }

        $bulan = (int) $request->input('bulan');
        $tahun = (int) $request->input('tahun');
        $jenisData = $request->input('jenis_data');
        $format = $request->input('format');

        $namaPosyandu = 'Semua Posyandu';
        if ($posyanduId !== 'all') {
            $pos = Posyandu::find($posyanduId);
            $namaPosyandu = $pos ? $pos->nama : "Posyandu #{$posyanduId}";
        }

        $namaBulan = Carbon::create()->month($bulan)->translatedFormat('F');
        $filename = "laporan_{$jenisData}_{$tahun}_{$bulan}_".Str::slug($namaPosyandu);

        if ($jenisData === 'profil_balita') {
            $query = Anak::withoutPosyanduScope()->with('posyandu')->where('status_aktif', true);
            if ($posyanduId !== 'all') {
                $query->where('posyandu_id', $posyanduId);
            }
            $data = $query->orderBy('nama', 'asc')->get();
        } elseif ($jenisData === 'pengukuran_bulanan') {
            $query = Pengukuran::withoutPosyanduScope()
                ->with(['anak.posyandu', 'pembuat'])
                ->where('bulan_ukur', $bulan)
                ->where('tahun_ukur', $tahun);

            if ($posyanduId !== 'all') {
                $query->whereHas('anak', function ($q) use ($posyanduId) {
                    $q->where('posyandu_id', $posyanduId);
                });
            }
            $data = $query->get()->sortBy(fn ($p) => $p->anak->nama ?? '');
        } else { // ranking_saw
            $query = HasilSaw::withoutPosyanduScope()
                ->with(['anak.posyandu', 'pengukuran'])
                ->where('bulan_ukur', $bulan)
                ->where('tahun_ukur', $tahun);

            if ($posyanduId !== 'all') {
                $query->where('posyandu_id', $posyanduId);
            }
            $data = $query->orderBy('nilai_v', 'desc')->get();
        }

        if ($format === 'excel') {
            return $this->exportCsvExcel($jenisData, $data, $namaPosyandu, $namaBulan, $tahun, $filename);
        }

        // PDF View
        return view('import_export.export_pdf', compact('jenisData', 'data', 'namaPosyandu', 'namaBulan', 'tahun', 'bulan'));
    }

    private function exportCsvExcel($jenisData, $data, $namaPosyandu, $namaBulan, $tahun, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function () use ($jenisData, $data, $namaPosyandu, $namaBulan, $tahun) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            if ($jenisData === 'profil_balita') {
                fputcsv($file, ["LAPORAN PROFIL BALITA - {$namaPosyandu}"]);
                fputcsv($file, ['Dicetak Pada: '.now()->translatedFormat('d F Y H:i')]);
                fputcsv($file, []);
                fputcsv($file, ['No', 'Posyandu', 'Nama Balita', 'NIK', 'Tanggal Lahir', 'Usia (Bulan)', 'Jenis Kelamin', 'Berat Lahir (Gram)', 'Status BBLR', 'Nama Orang Tua', 'Token Akses']);

                foreach ($data as $i => $row) {
                    fputcsv($file, [
                        $i + 1,
                        $row->posyandu->nama ?? '-',
                        $row->nama,
                        $row->nik ?? '-',
                        $row->tanggal_lahir ? $row->tanggal_lahir->format('d/m/Y') : '-',
                        $row->usia_bulan,
                        $row->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                        $row->berat_lahir_gram,
                        strtoupper($row->status_bblr),
                        $row->nama_orang_tua ?? '-',
                        $row->token_akses,
                    ]);
                }
            } elseif ($jenisData === 'pengukuran_bulanan') {
                fputcsv($file, ["LAPORAN PENGUKURAN BULANAN BALITA - {$namaPosyandu}"]);
                fputcsv($file, ["Periode: {$namaBulan} {$tahun}"]);
                fputcsv($file, ['Dicetak Pada: '.now()->translatedFormat('d F Y H:i')]);
                fputcsv($file, []);
                fputcsv($file, ['No', 'Posyandu', 'Nama Balita', 'NIK', 'Usia Saat Ukur (Bulan)', 'Tanggal Ukur', 'Tinggi Badan (cm)', 'Berat Badan (kg)', 'Petugas Encater']);

                foreach ($data as $i => $row) {
                    fputcsv($file, [
                        $i + 1,
                        $row->anak->posyandu->nama ?? '-',
                        $row->anak->nama ?? '-',
                        $row->anak->nik ?? '-',
                        $row->usia_bulan,
                        $row->tanggal_ukur ? $row->tanggal_ukur->format('d/m/Y') : '-',
                        $row->tinggi_cm,
                        $row->berat_kg,
                        $row->pembuat->name ?? '-',
                    ]);
                }
            } else { // ranking_saw
                fputcsv($file, ["LAPORAN HASIL RANKING SAW PRIORITAS STUNTING - {$namaPosyandu}"]);
                fputcsv($file, ["Periode: {$namaBulan} {$tahun}"]);
                fputcsv($file, ['Dicetak Pada: '.now()->translatedFormat('d F Y H:i')]);
                fputcsv($file, []);
                fputcsv($file, ['Peringkat', 'Posyandu', 'Nama Balita', 'Usia (Bulan)', 'TB (cm)', 'BB (kg)', 'Z-Score TB/U', 'Z-Score BB/U', 'Skor Preferensi (V)', 'Kategori Risiko', 'Status C2 Estimasi']);

                foreach ($data as $i => $row) {
                    fputcsv($file, [
                        $i + 1,
                        $row->posyandu->nama ?? '-',
                        $row->anak->nama ?? '-',
                        $row->pengukuran->usia_bulan ?? '-',
                        $row->pengukuran->tinggi_cm ?? '-',
                        $row->pengukuran->berat_kg ?? '-',
                        $row->z_tbu ?? 0,
                        $row->z_bbu ?? 0,
                        number_format($row->nilai_v, 4),
                        strtoupper($row->kategori_risiko),
                        $row->is_c2_estimasi ? 'Ya (BBLR Tidak Diketahui)' : 'Tidak',
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
