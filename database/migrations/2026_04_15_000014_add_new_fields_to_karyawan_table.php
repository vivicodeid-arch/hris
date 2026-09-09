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
            $table->string('npwp', 20)->nullable()->after('no_ktp');
            $table->string('alamat_sesuai_ktp', 255)->nullable()->after('alamat');
            $table->string('jurusan', 100)->nullable()->after('pendidikan_terakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn(['npwp', 'alamat_sesuai_ktp', 'jurusan']);
        });
    }
};
