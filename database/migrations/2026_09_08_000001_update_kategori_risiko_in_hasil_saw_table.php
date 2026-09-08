<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Converted all historical 'Sangat Tinggi' / 'sangat tinggi' records to 'Tinggi'
        DB::table('hasil_saw')
            ->whereIn('kategori_risiko', ['Sangat Tinggi', 'sangat tinggi', 'sangat_tinggi'])
            ->update(['kategori_risiko' => 'Tinggi']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed as categories are simplified
    }
};
