<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zscore_references', function (Blueprint $table) {
            $table->id();
            $table->enum('indicator', ['tbu', 'bbu']); // TB/U (Height-for-Age), BB/U (Weight-for-Age)
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->integer('usia_bulan');
            $table->double('l');
            $table->double('m');
            $table->double('s');
            $table->double('sd3neg')->nullable();
            $table->double('sd2neg')->nullable();
            $table->double('sd1neg')->nullable();
            $table->double('sd0')->nullable();
            $table->double('sd1')->nullable();
            $table->double('sd2')->nullable();
            $table->double('sd3')->nullable();
            $table->timestamps();

            $table->unique(['indicator', 'jenis_kelamin', 'usia_bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zscore_references');
    }
};
