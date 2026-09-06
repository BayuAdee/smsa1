<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kbm_references', function (Blueprint $table) {
            $table->id();
            $table->integer('usia_bulan')->unique();
            $table->integer('kbm_gram'); // Kenaikan Berat Badan Minimal dalam gram
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kbm_references');
    }
};
