# Progress Pembangunan Sistem Monitoring Stunting & SPK SAW

## Status Fase
- [x] **Fase 1: Migrations, Models, dan Seeders** (Selesai)
- [x] **Fase 2: Auth System, Scope Middleware, & Routing Dasar** (Selesai)
- [x] **Fase 3: Landing Page Publik (`/`) & Halaman Akses Orang Tua (`/ortu/{token}`)** (Selesai)
- [x] **Fase 4: Core Services (`ZscoreService`, `GrowthFalteringService`, `SawCalculatorService`)** (Selesai)
- [x] **Fase 5: UI Internal Dashboard, Form Input Balita, Pengukuran, & Tabel Ranking SAW** (Selesai)

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
