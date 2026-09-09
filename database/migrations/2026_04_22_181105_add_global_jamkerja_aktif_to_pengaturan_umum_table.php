<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_umum', function (Blueprint $table) {
            $table->boolean('global_jamkerja_aktif')->default(0)->after('sistem_hari_kerja');
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_umum', function (Blueprint $table) {
            $table->dropColumn('global_jamkerja_aktif');
        });
    }
};
