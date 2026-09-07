# Progress Pembangunan Sistem Monitoring Stunting & SPK SAW

## Status Fase
- [x] **Fase 1: Migrations, Models, dan Seeders** (Selesai)
- [x] **Fase 2: Auth System, Scope Middleware, & Routing Dasar** (Selesai)
- [x] **Fase 3: Landing Page Publik (`/`) & Halaman Akses Orang Tua (`/ortu/{token}`)** (Selesai)
- [x] **Fase 4: Core Services (`ZscoreService`, `GrowthFalteringService`, `SawCalculatorService`)** (Selesai)
- [x] **Fase 5: UI Internal Dashboard, Form Input Balita, Pengukuran, & Tabel Ranking SAW** (Selesai)
- [x] **Fase 6: Refactoring Alur Input Pengukuran Bulanan & Perangkingan SAW Berbasis Periode Bulan (Month/Year Session)** (Selesai)
- [x] **Fase 7: Fitur Client-Side (Search Real-time >=3 Karakter & Toggle Filter "Belum Diukur")** (Selesai)
- [x] **Fase 8: Revisi Modul Manajemen Posyandu (Edit, Soft-Status Toggle, & Unique Validation)** (Selesai)
- [x] **Fase 9: Dedicated Menu & Halaman Import/Export Data Berbasis Role** (Selesai)

---

## Log Ringkasan Pengerjaan

### Fase 1: Migrations, Models, dan Seeders
- Inisialisasi struktur database:
  - `posyandus`: Data master Posyandu (id, nama, wilayah).
  - `users`: Penambahan kolom `posyandu_id` (nullable) dan `role` ('kader', 'bidan').
  - `anaks`: Data Balita (posyandu_id, nama, tanggal_lahir, jenis_kelamin, berat_lahir_gram, status_bblr, token_akses UUID, status_aktif, nama_orang_tua).
  - `pengukurans`: Catatan fisik bulanan (anak_id, tanggal_ukur, usia_bulan, tinggi_cm, berat_kg, dibuat_oleh).
  - `hasil_saw`: Hasil perhitungan SPK SAW (anak_id, pengukuran_id, z_tbu, z_bbu, raw_c1..c4, r_c1..c4, nilai_v, kategori_risiko, is_c2_estimasi, dihitung_pada).
  - `zscore_references` & `kbm_references`: Tabel acuan standar WHO LMS (Z-score TB/U & BB/U) dan Kenaikan Berat Badan Minimal (KBM).
- Penyiapan file acuan CSV (`database/seeders/data/zscore_references.csv` & `kbm_references.csv`).
- Pembuatan Seeder lengkap (`DatabaseSeeder`) berisi data acuan WHO, data Posyandu dummy, data User Kader & Bidan, Balita dummy, dan Pengukuran awal.

### Fase 6: Refactoring Alur Input Pengukuran Bulanan & Perangkingan SAW Berbasis Periode Bulan (Month/Year Session)
- **Schema & Database Audit**:
  - Penambahan kolom `bulan_ukur` (1-12) & `tahun_ukur` serta unique index `[anak_id, bulan_ukur, tahun_ukur]` di tabel `pengukurans` untuk mendukung logika **Timpa Data (Upsert)**.
  - Penambahan `posyandu_id`, `bulan_ukur`, dan `tahun_ukur` serta unique constraint `[anak_id, bulan_ukur, tahun_ukur]` di tabel `hasil_saw`.
  - Migrasi otomatis melakukan deduplikasi & backfill data pengukuran eksisting.
- **Service Layer (`SawCalculatorService.php`)**:
  - Implementasi method `hitungUntukPosyanduPeriode($posyandu_id, $bulan_ukur, $tahun_ukur)` untuk mengisolasi matrik normalisasi SAW hanya kepada anak yang memiliki pengukuran aktif di periode bulan tersebut.
- **UI/UX Input Pengukuran Bulanan (`pengukuran/index.blade.php`)**:
  - Header Konfirmasi Periode (Bulan & Tahun).
  - Tampilan daftar balita posyandu (Card Grid Mobile-First & Table) dengan status badge: `[Belum Diukur]` (Abu-abu) & `[Sudah Diukur: 85 cm / 11.5 kg]` (Badge Hijau).
  - Popup Modal Responsif Fast Mobile UI (hanya input TB cm & BB kg) dengan AJAX POST handler & instant update badge tanpa full page reload.
  - Automatic calculation usia bulan & tanggal ukur sesuai periode aktif & tanggal lahir anak.
- **Filter Periode SAW Ranking & Dashboard**:
  - Dropdown Filter Periode di Halaman Ranking SAW & Dashboard.
  - Hasil ranking & statistik berisiko tinggi terikat dan terfilter per Periode Bulan & Tahun.
- **Pengujian & Verifikasi**:
  - Penambahan unit & feature test `PeriodPengukuranSawTest.php`. 100% test suite passing (15 tests, 53 assertions).

### Fase 7: Fitur Client-Side (Search Real-time >=3 Karakter & Toggle Filter "Belum Diukur")
- **Search Real-Time Nama Balita**:
  - Input text pencarian "Cari Nama Balita...".
  - Logika pencarian instan pada client-side DOM hanya aktif jika panjang input >= 3 karakter (`inputValue.length >= 3`). Jika 0–2 karakter, seluruh data ditampilkan tanpa penyaringan untuk mencegah UI flickering.
- **Toggle Filter "Belum Diukur"**:
  - Tombol chip toggle "Semua Balita" vs "Belum Diukur".
  - Saat mode "Belum Diukur" aktif, kartu balita yang sudah memiliki pengukuran di periode tersebut tersembunyi secara instan sehingga Kader dapat berfokus menyelesaikan balita yang belum diukur.
- **Dynamic Re-Filtering pada Input Modal**:
  - Setelah input pengukuran disubmit via Modal AJAX, atribut `data-sudah` dan badge kartu ter-update secara otomatis, serta fungsi re-filtering dipanggil secara instan tanpa reload halaman.

### Fase 8: Revisi Modul Manajemen Posyandu (Edit, Soft-Status Toggle, & Unique Validation)
- **Skema Database & Migrasi (`2026_09_07_000002_add_is_active_and_unique_nama_to_posyandus_table.php`)**:
  - Penambahan kolom `is_active` (boolean, default true) dan constraint unique `posyandus_nama_unique` pada tabel `posyandus`.
- **Fitur Tambah & Edit Posyandu**:
  - Validasi unik saat membuat Posyandu baru (`nama => unique:posyandus,nama`) dengan pesan error custom *"Nama Posyandu sudah terdaftar."*
  - Fitur Edit Posyandu (Modal Edit) dengan validasi unik mengabaikan ID posyandu itu sendiri (`Rule::unique('posyandus', 'nama')->ignore($posyandu->id)`).
- **Soft Status Active/Non-Active Toggle**:
  - Menghapus fitur Hard Delete (`destroy`).
  - Menambahkan action toggle status (Nonaktifkan / Aktifkan) via route `PATCH /posyandu/{id}/toggle`.
  - Unit posyandu non-aktif (`is_active = false`) disembunyikan dari pilihan dropdown registrasi balita/kader baru, namun seluruh data historis balita & pengukurannya tetap utuh dan aman.
- **Tampilan Tabel/Card Posyandu**:
  - Visual status badge: `[Aktif]` (Hijau) & `[Non-Aktif]` (Abu-abu).
  - Posyandu yang non-aktif otomatis ditampilkan pada urutan paling bawah (`orderBy('is_active', 'desc')->orderBy('nama', 'asc')`).
- **Pengujian**:
  - Penambahan feature test `PosyanduManagementTest.php` (100% test suite passing, 19 tests, 75 assertions).

### Fase 9: Dedicated Menu & Halaman Import/Export Data Berbasis Role
- **Penambahan Navigation Sidebar**:
  - Menambahkan item menu internal: `📥 Import & Export Data` (`route('import-export.index')`) yang dapat diakses oleh Kader maupun Bidan.
- **Authorization & Gate (`import-data`)**:
  - Registrasi Gate `import-data` di `AppServiceProvider.php` untuk memproteksi endpoint `downloadTemplate`, `previewImport`, dan `executeImport`.
  - Kader yang mencoba melakukan POST request ke endpoint import akan langsung mendapatkan proteksi HTTP 403 Forbidden.
- **Fitur Import Data Balita (Khusus Role Bidan)**:
  - Tombol aksi "Download Template (.csv)" untuk mengunduh berkas acuan CSV yang ramah Excel (UTF-8 BOM header).
  - Alur Pratinjau Validasi 2-Step (`previewImport` -> `executeImport`):
    * Memvalidasi keberadaan ID Posyandu, kelengkapan nama, format NIK, tanggal lahir (format YYYY-MM-DD & tidak di masa depan), jenis kelamin (L/P), berat lahir gram (500–6000g), dan status BBLR (dengan auto-normalize).
    * Menampilkan tabel pratinjau dengan indikator badge warna: `[Valid - Hijau]` dan `[Error - Merah]` beserta rincian error per baris.
    * Tombol "Eksekusi Import" hanya memproses dan memasukkan baris-baris data yang valid ke tabel `anaks` dengan generate `token_akses` unik secara otomatis.
- **Fitur Export Data Laporan (Role Bidan & Kader)**:
  - Form Filter Export dengan opsi Scope Posyandu, Periode Bulan & Tahun, Jenis Data Laporan (`profil_balita`, `pengukuran_bulanan`, `ranking_saw`), dan Format Output (`excel` / `.csv` atau `pdf`).
  - Penguncian Scope Kader: Jika login sebagai Kader, pilihan Posyandu otomatis dikunci pada `posyandu_id` milik kader tersebut dan disajikan dalam bentuk badge readonly terkunci.
  - Generasi berkas Excel (.csv stream) dan PDF pratinjau cetak resmi lengkap dengan blok tanda tangan Kader & Bidan Desa.
- **Pengujian & Verifikasi**:
  - Penambahan Feature Test `ImportExportTest.php` (9 tests, 28 assertions).
  - Total test suite aplikasi: 100% passing (30 tests, 109 assertions).
