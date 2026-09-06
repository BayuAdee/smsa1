<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_saw', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anaks')->cascadeOnDelete();
            $table->foreignId('pengukuran_id')->constrained('pengukurans')->cascadeOnDelete();
            $table->double('z_tbu');
            $table->double('z_bbu');
            $table->double('raw_c1');
            $table->double('raw_c2');
            $table->double('raw_c3');
            $table->double('raw_c4');
            $table->double('r_c1');
            $table->double('r_c2');
            $table->double('r_c3');
            $table->double('r_c4');
            $table->double('nilai_v');
            $table->string('kategori_risiko');
            $table->boolean('is_c2_estimasi')->default(false);
            $table->timestamp('dihitung_pada')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_saw');
    }
};
