<?php

namespace App\Console\Commands;

use App\Models\HasilSaw;
use App\Models\Posyandu;
use App\Services\SawCalculatorService;
use Illuminate\Console\Command;

class RecalculateSaw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saw:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kalkulasi ulang seluruh data matriks Hasil SPK SAW berdasarkan 3 kategori risiko baru';

    /**
     * Execute the console command.
     */
    public function handle(SawCalculatorService $sawCalculatorService): int
    {
        $this->info('Memulai recalculate seluruh data SPK SAW...');

        // Ambil kombinasi (bulan_ukur, tahun_ukur) unik dari tabel hasil_saw atau pengukurans
        $periodes = HasilSaw::withoutGlobalScope('posyandu_scope')
            ->select('bulan_ukur', 'tahun_ukur')
            ->distinct()
            ->get();

        if ($periodes->isEmpty()) {
            $this->warn('Tidak ada data hasil_saw yang ditemukan.');

            return Command::SUCCESS;
        }

        $posyandus = Posyandu::all();

        $count = 0;
        foreach ($periodes as $p) {
            // Recalculate untuk gabungan (all posyandu)
            $sawCalculatorService->hitungUntukPosyanduPeriode(null, $p->bulan_ukur, $p->tahun_ukur);

            // Recalculate per posyandu
            foreach ($posyandus as $pos) {
                $sawCalculatorService->hitungUntukPosyanduPeriode($pos->id, $p->bulan_ukur, $p->tahun_ukur);
            }
            $count++;
        }

        $this->info("Recalculate selesai! Total {$count} periode berhasil di-recalculate.");

        return Command::SUCCESS;
    }
}
