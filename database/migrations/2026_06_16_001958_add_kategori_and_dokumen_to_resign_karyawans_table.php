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
        Schema::table('resign_karyawans', function (Blueprint $table) {
            $table->string('kategori', 50)->nullable()->after('tanggal_resign');
            $table->string('dokumen', 255)->nullable()->after('alasan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resign_karyawans', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'dokumen']);
        });
    }
};
