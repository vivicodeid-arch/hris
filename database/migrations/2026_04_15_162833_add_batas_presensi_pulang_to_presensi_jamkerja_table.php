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
        Schema::table('presensi_jamkerja', function (Blueprint $table) {
            $table->time('batas_presensi_pulang')->nullable()->after('lintashari')
                ->comment('Batas jam presensi pulang lintas hari (per jam kerja). Jika kosong, gunakan nilai dari general setting.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi_jamkerja', function (Blueprint $table) {
            $table->dropColumn('batas_presensi_pulang');
        });
    }
};
