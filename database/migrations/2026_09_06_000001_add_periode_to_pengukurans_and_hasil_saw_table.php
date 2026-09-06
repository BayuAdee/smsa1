<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pengukurans', 'bulan_ukur')) {
            Schema::table('pengukurans', function (Blueprint $table) {
                $table->unsignedTinyInteger('bulan_ukur')->nullable()->after('tanggal_ukur');
                $table->unsignedSmallInteger('tahun_ukur')->nullable()->after('bulan_ukur');
            });
        }

        // Backfill existing pengukurans using PHP Carbon
        DB::table('pengukurans')->whereNull('bulan_ukur')->get()->each(function ($p) {
            $date = Carbon::parse($p->tanggal_ukur);
            DB::table('pengukurans')->where('id', $p->id)->update([
                'bulan_ukur' => $date->month,
                'tahun_ukur' => $date->year,
            ]);
        });

        // Deduplicate pengukurans (keep the latest ID for each anak_id, bulan_ukur, tahun_ukur)
        $duplicates = DB::table('pengukurans')
            ->select('anak_id', 'bulan_ukur', 'tahun_ukur', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as count'))
            ->groupBy('anak_id', 'bulan_ukur', 'tahun_ukur')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('pengukurans')
                ->where('anak_id', $dup->anak_id)
                ->where('bulan_ukur', $dup->bulan_ukur)
                ->where('tahun_ukur', $dup->tahun_ukur)
                ->where('id', '<', $dup->keep_id)
                ->delete();
        }

        Schema::table('pengukurans', function (Blueprint $table) {
            $table->unique(['anak_id', 'bulan_ukur', 'tahun_ukur'], 'pengukurans_anak_periode_unique');
        });

        if (! Schema::hasColumn('hasil_saw', 'posyandu_id')) {
            Schema::table('hasil_saw', function (Blueprint $table) {
                $table->foreignId('posyandu_id')->nullable()->after('anak_id')->constrained('posyandus')->nullOnDelete();
                $table->unsignedTinyInteger('bulan_ukur')->nullable()->after('pengukuran_id');
                $table->unsignedSmallInteger('tahun_ukur')->nullable()->after('bulan_ukur');
            });
        }

        // Backfill existing hasil_saw from pengukurans & anaks
        DB::table('hasil_saw')->whereNull('bulan_ukur')->get()->each(function ($hs) {
            $p = DB::table('pengukurans')->find($hs->pengukuran_id);
            $a = DB::table('anaks')->find($hs->anak_id);
            if ($p) {
                $date = Carbon::parse($p->tanggal_ukur);
                DB::table('hasil_saw')->where('id', $hs->id)->update([
                    'posyandu_id' => $a ? $a->posyandu_id : null,
                    'bulan_ukur' => $p->bulan_ukur ?? $date->month,
                    'tahun_ukur' => $p->tahun_ukur ?? $date->year,
                ]);
            }
        });

        // Deduplicate hasil_saw (keep the latest ID for each anak_id, bulan_ukur, tahun_ukur)
        $sawDuplicates = DB::table('hasil_saw')
            ->select('anak_id', 'bulan_ukur', 'tahun_ukur', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as count'))
            ->groupBy('anak_id', 'bulan_ukur', 'tahun_ukur')
            ->having('count', '>', 1)
            ->get();

        foreach ($sawDuplicates as $dup) {
            DB::table('hasil_saw')
                ->where('anak_id', $dup->anak_id)
                ->where('bulan_ukur', $dup->bulan_ukur)
                ->where('tahun_ukur', $dup->tahun_ukur)
                ->where('id', '<', $dup->keep_id)
                ->delete();
        }

        Schema::table('hasil_saw', function (Blueprint $table) {
            $table->unique(['anak_id', 'bulan_ukur', 'tahun_ukur'], 'hasil_saw_anak_periode_unique');
        });
    }

    public function down(): void
    {
        Schema::table('hasil_saw', function (Blueprint $table) {
            $table->dropUnique('hasil_saw_anak_periode_unique');
            $table->dropForeign(['posyandu_id']);
            $table->dropColumn(['posyandu_id', 'bulan_ukur', 'tahun_ukur']);
        });

        Schema::table('pengukurans', function (Blueprint $table) {
            $table->dropUnique('pengukurans_anak_periode_unique');
            $table->dropColumn(['bulan_ukur', 'tahun_ukur']);
        });
    }
};
