<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained('posyandus')->cascadeOnDelete();
            $table->string('nama');
            $table->string('nik')->nullable();
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->integer('berat_lahir_gram')->default(3000);
            $table->enum('status_bblr', ['tidak', 'bblr', 'tidak_diketahui'])->default('tidak');
            $table->string('token_akses')->unique();
            $table->boolean('status_aktif')->default(true);
            $table->string('nama_orang_tua')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anaks');
    }
};
