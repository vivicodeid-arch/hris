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
        Schema::table('presensi_izincuti', function (Blueprint $table) {
            $table->string('pelimpahan_tugas')->nullable()->after('keterangan');
            $table->string('nama_kepala_divisi')->nullable()->after('pelimpahan_tugas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi_izincuti', function (Blueprint $table) {
            $table->dropColumn(['pelimpahan_tugas', 'nama_kepala_divisi']);
        });
    }
};
