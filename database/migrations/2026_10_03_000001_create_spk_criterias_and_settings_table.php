<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spk_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique(); // C1, C2, C3, C4
            $table->string('nama');
            $table->decimal('bobot', 5, 4); // Misal 0.4500 (45%)
            $table->enum('jenis', ['cost', 'benefit'])->default('cost');
            $table->string('tipe_sumber')->default('kustom'); // otomatis_tbu, otomatis_growth_faltering, otomatis_bbu, otomatis_bblr, kustom
            $table->json('sub_kriteria')->nullable(); // Pilihan opsi/label & nilai skor jika kustom
            $table->string('keterangan')->nullable();
            $table->integer('urutan')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('spk_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spk_settings');
        Schema::dropIfExists('spk_criterias');
    }
};
