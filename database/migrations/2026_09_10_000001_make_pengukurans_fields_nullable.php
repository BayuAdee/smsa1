<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengukurans', function (Blueprint $table) {
            $table->decimal('tinggi_cm', 5, 2)->nullable()->change();
            $table->decimal('berat_kg', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengukurans', function (Blueprint $table) {
            $table->decimal('tinggi_cm', 5, 2)->nullable(false)->change();
            $table->decimal('berat_kg', 5, 2)->nullable(false)->change();
        });
    }
};
