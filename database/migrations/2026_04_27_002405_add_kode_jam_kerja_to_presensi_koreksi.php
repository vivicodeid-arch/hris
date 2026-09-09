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
        Schema::table('presensi_koreksi', function (Blueprint $table) {
            $table->char('kode_jam_kerja', 4)->nullable()->after('tanggal');
            $table->foreign('kode_jam_kerja')->references('kode_jam_kerja')->on('presensi_jamkerja')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi_koreksi', function (Blueprint $table) {
            $table->dropForeign(['kode_jam_kerja']);
            $table->dropColumn('kode_jam_kerja');
        });
    }
};
