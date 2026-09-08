<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use Illuminate\Http\Request;

class OrtuController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function checkToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = str_replace(' ', '', strtoupper(trim($request->token)));

        $anak = Anak::withoutGlobalScope('posyandu_scope')
            ->where('token_akses', $token)
            ->first();

        if (! $anak) {
            return back()->withInput()->with('error', 'Kode token anak tidak ditemukan. Silakan periksa kembali token unik dari Posyandu.');
        }

        return redirect()->route('ortu.show', ['token' => $anak->token_akses]);
    }

    public function show($token)
    {
        $token = str_replace(' ', '', strtoupper(trim($token)));

        $anak = Anak::withoutGlobalScope('posyandu_scope')
            ->where('token_akses', $token)
            ->with([
                'posyandu' => function ($q) {
                    $q->withoutGlobalScope('posyandu_scope');
                },
                'pengukurans' => function ($q) {
                    $q->withoutGlobalScope('posyandu_scope')->orderBy('tanggal_ukur', 'asc');
                },
                'hasilSawTerakhir' => function ($q) {
                    $q->withoutGlobalScope('posyandu_scope');
                },
            ])
            ->firstOrFail();

        $pengukuransChart = $anak->pengukurans->sortBy('tanggal_ukur')->values();
        $pengukuransTable = $anak->pengukurans->sortByDesc('tanggal_ukur')->values();
        $hasilSaw = $anak->hasilSawTerakhir;

        // Formulate layman explanation for stunting & weight status
        $laymanStatus = $this->getLaymanStatus($hasilSaw, $pengukuransChart->last());

        return view('ortu.show', compact('anak', 'pengukuransChart', 'pengukuransTable', 'hasilSaw', 'laymanStatus'));
    }

    private function getLaymanStatus($hasilSaw, $pengukuranTerakhir)
    {
        if (! $hasilSaw || ! $pengukuranTerakhir) {
            return [
                'badge_color' => 'bg-sky-500/20 text-sky-300 border-sky-500/30',
                'title' => 'Belum Ada Perhitungan SAW',
                'description' => 'Balita ini baru terdaftar dan belum memiliki data pengukuran yang lengkap.',
                'tips' => [
                    'Bawa balita ke Posyandu rutin setiap bulan.',
                    'Pastikan balita mendapatkan ASI Eksklusif dan MPASI bergizi seimbang.',
                ],
            ];
        }

        $kategori = strtolower((string) $hasilSaw->kategori_risiko);

        if (in_array($kategori, ['tinggi', 'sangat tinggi', 'sangat_tinggi'])) {
            return [
                'badge_color' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                'badge_text' => 'Perhatian Khusus - Risiko Tinggi',
                'title' => 'Memerlukan Perhatian dan Pendampingan Gizi Segera',
                'description' => 'Tinggi badan si kecil berada di bawah rata-rata pertumbuhan anak seusianya. Perlu konsultasi lebih lanjut dengan Bidan Desa dan Petugas Puskesmas.',
                'tips' => [
                    'Konsultasikan dengan Bidan Desa / Dokter di Puskesmas terdekat.',
                    'Berikan makanan tinggi protein hewani (telur, ikan, daging ayam/sapi) setiap hari.',
                    'Pastikan imunisasi lengkap dan berikan vitamin A sesuai jadwal Posyandu.',
                ],
            ];
        } elseif ($kategori === 'sedang') {
            return [
                'badge_color' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                'badge_text' => 'Risiko Sedang - Perlu Pengawasan',
                'title' => 'Pertumbuhan Tinggi Perlu Dioptimalkan',
                'description' => 'Kenaikan tinggi/berat badan si kecil perlu dipantau secara ketat agar tidak tertinggal dari grafik tumbuh kembang ideal.',
                'tips' => [
                    'Variasikan MPASI dengan asupan protein dan kalori yang cukup.',
                    'Pantau terus penimbangan berat dan tinggi badan di Posyandu bulan depan.',
                    'Menjaga kebersihan lingkungan dan sanitasi air minum rumah tangga.',
                ],
            ];
        } else {
            return [
                'badge_color' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                'badge_text' => 'Pertumbuhan Optimal & Sehat (Risiko Rendah)',
                'title' => 'Selamat! Si Kecil Tumbuh Optimal & Sehat',
                'description' => 'Tinggi dan berat badan si kecil sesuai dengan grafik pertumbuhan anak sehat WHO.',
                'tips' => [
                    'Pertahankan pola makan bergizi seimbang dan pola hidup bersih.',
                    'Tetap rutin hadir di Posyandu setiap bulan untuk memantau tumbuh kembangnya.',
                ],
            ];
        }
    }
}
