<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->decimal('jam_lembur_aktual', 5, 2)->nullable()->after('denda');
            $table->decimal('jam_lembur_netto', 5, 2)->nullable()->after('jam_lembur_aktual');
            $table->integer('nominal_lembur')->nullable()->after('jam_lembur_netto');
            $table->boolean('is_lembur_khusus')->default(false)->after('nominal_lembur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropColumn(['jam_lembur_aktual', 'jam_lembur_netto', 'nominal_lembur', 'is_lembur_khusus']);
        });
    }
};
