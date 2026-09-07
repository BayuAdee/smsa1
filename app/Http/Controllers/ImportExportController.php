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

            // Add sample row from user specification with NIK formatted as Excel text string
            fputcsv($file, [
                'Budi Santoso',
                '="3201011234567890"',
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

        $rawItems = [];
        $fileNiks = [];

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

                // Clean NIK for counting file occurrences
                $nikClean = trim($row['nik'] ?? '');
                if (! empty($nikClean)) {
                    if (str_starts_with($nikClean, '="') && str_ends_with($nikClean, '"')) {
                        $nikClean = substr($nikClean, 2, -1);
                    }
                    $nikClean = ltrim($nikClean, "'`\"");
                    if (is_numeric($nikClean) && (str_contains($nikClean, 'E+') || str_contains($nikClean, 'e+'))) {
                        $nikClean = sprintf('%.0f', (float) $nikClean);
                    }
                    $fileNiks[] = $nikClean;
                }

                $rawItems[] = [
                    'row_number' => $rowNumber,
                    'row' => $row,
                    'nik_clean' => ! empty($nikClean) ? $nikClean : null,
                ];
            }
            fclose($handle);
        }

        $nikCounts = array_count_values(array_filter($fileNiks));

        $rows = [];
        foreach ($rawItems as $item) {
            $rowNumber = $item['row_number'];
            $row = $item['row'];
            $nik = $item['nik_clean'];

            $errors = [];

            // 1. Posyandu validation (supports Posyandu Name or Posyandu ID)
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

            // 2. Nama validation
            $nama = trim($row['nama'] ?? '');
            if (empty($nama)) {
                $errors[] = 'Nama balita wajib diisi.';
            }

            // NIK Length validation
            if (! empty($nik) && strlen($nik) > 20) {
                $errors[] = 'NIK tidak boleh melebihi 20 karakter.';
            }

            // 3. Tanggal lahir validation (supports DD-MM-YYYY, YYYY-MM-DD, DD/MM/YYYY)
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

            // 4. Jenis Kelamin
            $jk = strtoupper(trim($row['jenis_kelamin'] ?? ''));
            if (! in_array($jk, ['L', 'P'])) {
                $errors[] = 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).';
            }

            // 5. Berat Lahir Gram
            $beratGram = filter_var($row['berat_lahir_gram'] ?? null, FILTER_VALIDATE_INT);
            if ($beratGram === false || $beratGram < 500 || $beratGram > 6000) {
                $errors[] = 'Berat lahir (gram) harus berupa angka antara 500g - 6000g.';
            }

            // 6. Status BBLR
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

            // 7. Nama Orang Tua
            $namaOrtu = trim($row['nama_orang_tua'] ?? '');

            // === 3 ATURAN VALIDASI DUPLIKASI DATA BALITA ===

            // Rule 1a: Duplikat di Dalam File Import (In-File Check)
            if (! empty($nik) && ($nikCounts[$nik] ?? 0) > 1) {
                $errors[] = 'Duplikat di dalam file import.';
            }

            // Rule 1b: Pencocokan NIK dengan Database (DB NIK Match)
            if (! empty($nik)) {
                $nikExistsInDb = Anak::withoutGlobalScope('posyandu_scope')
                    ->where('nik', $nik)
                    ->exists();
                if ($nikExistsInDb) {
                    $errors[] = 'Data anak sudah terdaftar berdasarkan NIK.';
                }
            }

            // Rule 1c: Pencocokan Komposit saat NIK Kosong (DB Soft Match)
            if (empty($nik) && ! empty($nama) && $parsedDate && ! empty($namaOrtu)) {
                $compositeExistsInDb = Anak::withoutGlobalScope('posyandu_scope')
                    ->where('nama', 'like', $nama)
                    ->whereDate('tanggal_lahir', $parsedDate->format('Y-m-d'))
                    ->where('nama_orang_tua', 'like', $namaOrtu)
                    ->exists();
                if ($compositeExistsInDb) {
                    $errors[] = 'Kemungkinan data anak duplikat.';
                }
            }

            $isValid = empty($errors);

            $rows[] = [
                'row_number' => $rowNumber,
                'is_valid' => $isValid,
                'errors' => $errors,
                'raw_csv' => [
                    'nama' => $row['nama'] ?? '',
                    'nik' => $row['nik'] ?? '',
                    'tanggal_lahir' => $row['tanggal_lahir'] ?? '',
                    'jenis_kelamin' => $row['jenis_kelamin'] ?? '',
                    'berat_lahir_gram' => $row['berat_lahir_gram'] ?? '',
                    'status_bblr' => $row['status_bblr'] ?? '',
                    'nama_orang_tua' => $row['nama_orang_tua'] ?? '',
                    'posyandu' => $row['posyandu'] ?? $row['posyandu_id'] ?? '',
                ],
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

    public function downloadFailedLog()
    {
        Gate::authorize('import-data');

        $preview = session('import_preview_data');

        if (! $preview || empty($preview['rows'])) {
            return redirect()->route('import-export.index', ['tab' => 'import'])
                ->withErrors(['import' => 'Tidak ada data pratinjau import. Silakan unggah file terlebih dahulu.']);
        }

        $failedRows = array_values(array_filter($preview['rows'], fn ($r) => ! $r['is_valid']));

        if (empty($failedRows)) {
            return redirect()->route('import-export.index', ['tab' => 'import'])
                ->with('success', 'Seluruh baris data pada file valid, tidak ada baris yang gagal!');
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="baris_gagal_import.csv"',
        ];

        $callback = function () use ($failedRows) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
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
                'alasan_gagal',
            ]);

            foreach ($failedRows as $row) {
                $raw = $row['raw_csv'] ?? [];
                $nikVal = ! empty($raw['nik']) ? '="'.$raw['nik'].'"' : (! empty($row['data']['nik']) ? '="'.$row['data']['nik'].'"' : '');

                fputcsv($file, [
                    $raw['nama'] ?? $row['data']['nama'],
                    $nikVal,
                    $raw['tanggal_lahir'] ?? $row['data']['tanggal_lahir'],
                    $raw['jenis_kelamin'] ?? $row['data']['jenis_kelamin'],
                    $raw['berat_lahir_gram'] ?? $row['data']['berat_lahir_gram'],
                    $raw['status_bblr'] ?? $row['data']['status_bblr'],
                    $raw['nama_orang_tua'] ?? $row['data']['nama_orang_tua'],
                    $raw['posyandu'] ?? $row['data']['posyandu_input'],
                    implode('; ', $row['errors']),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
                $tokenAkses = Anak::generateTokenAkses();

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
                    $nikFormatted = ! empty($row->nik) ? '="'.$row->nik.'"' : '-';
                    fputcsv($file, [
                        $i + 1,
                        $row->posyandu->nama ?? '-',
                        $row->nama,
                        $nikFormatted,
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
                    $nikFormatted = ! empty($row->anak->nik) ? '="'.$row->anak->nik.'"' : '-';
                    fputcsv($file, [
                        $i + 1,
                        $row->anak->posyandu->nama ?? '-',
                        $row->anak->nama ?? '-',
                        $nikFormatted,
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
