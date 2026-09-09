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
        Schema::table('karyawan', function (Blueprint $table) {
            $table->string('email', 100)->nullable()->after('no_hp');
            $table->string('kontak_darurat', 100)->nullable()->after('email');
            $table->string('hubungan_kontak_darurat', 50)->nullable()->after('kontak_darurat');
            $table->string('nama_bank', 50)->nullable()->after('hubungan_kontak_darurat');
            $table->string('no_rekening', 30)->nullable()->after('nama_bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn(['email', 'kontak_darurat', 'hubungan_kontak_darurat', 'nama_bank', 'no_rekening']);
        });
    }
};
