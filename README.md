# SiStunting SAW - Sistem Monitoring & SPK Stunting Posyandu 

Aplikasi monitoring perkembangan balita dan Sistem Pendukung Keputusan (SPK) penentuan prioritas penanganan stunting menggunakan metode **Simple Additive Weighting (SAW)** dan standar **Z-Score WHO**.

---

## Fitur Utama

- **Sistem SPK SAW**: Perhitungan otomatis tingkat risiko stunting (*Tinggi*, *Sedang*, *Rendah*) berbasis periode pengukuran.
- **Multi-Role & Scope Data**:
  - **Bidan**: Akses global seluruh posyandu, manajemen posyandu, dan impor data masal.
  - **Kader**: Akses terbatas pada wilayah posyandu masing-masing dan ekspor laporan.
- **Import & Export Data**: Alur 2-step (Upload & Preview) lengkap dengan deteksi duplikasi NIK dan profil balita.
- **Pencarian Cepat (AJAX)**: Pencarian balita real-time tanpa *page-reload* dengan proteksi *debounce*.
- **Mobile-First & Dual Theme**: Dioptimalkan untuk ponsel dengan pilihan *Light Mode* (default) dan *Dark Mode*.
- **Akses Orang Tua**: Fitur cek perkembangan balita via token tanpa perlu login.

---

## Teknologi

- **Backend**: Laravel 10+ / PHP 8.x
- **Database**: MySQL
- **Frontend**: Tailwind CSS, Alpine.js, Blade Views
- **Standar Referensi**: WHO Child Growth Standards (LMS & Z-Score)

---

## Panduan Instalasi Lokal

### 1. Persiapan Database
Nyalakan MySQL (via Laragon / XAMPP) dan buat database kosong bernama `stunting_db` di **phpMyAdmin**.

### 2. Setup Projek
Buka terminal di folder projek, lalu jalankan perintah berikut:

```bash
# Install dependency PHP & JavaScript
composer install
npm install && npm run build

# Salin environment file
cp .env.example .env

# Generate application key
php artisan key:generate