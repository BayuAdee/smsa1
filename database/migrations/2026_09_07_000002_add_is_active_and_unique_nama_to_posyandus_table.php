<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posyandus', 'is_active')) {
            Schema::table('posyandus', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('wilayah');
            });
        }

        // Deduplicate posyandus names before adding unique constraint if necessary
        $duplicates = DB::table('posyandus')
            ->select('nama', DB::raw('COUNT(*) as count'))
            ->groupBy('nama')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $items = DB::table('posyandus')->where('nama', $dup->nama)->get();
            foreach ($items->skip(1) as $index => $item) {
                DB::table('posyandus')->where('id', $item->id)->update([
                    'nama' => $item->nama.' ('.($index + 2).')',
                ]);
            }
        }

        Schema::table('posyandus', function (Blueprint $table) {
            $table->unique('nama', 'posyandus_nama_unique');
        });
    }

    public function down(): void
    {
        Schema::table('posyandus', function (Blueprint $table) {
            $table->dropUnique('posyandus_nama_unique');
            $table->dropColumn('is_active');
        });
    }
};
