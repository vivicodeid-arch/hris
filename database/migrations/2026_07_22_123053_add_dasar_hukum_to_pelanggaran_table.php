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
        Schema::table('pelanggaran', function (Blueprint $table) {
            $table->string('no_kontrak', 255)->nullable()->after('no_dokumen');
            $table->text('pasal_pelanggaran')->nullable()->after('no_kontrak');
            $table->text('dasar_uu')->nullable()->after('pasal_pelanggaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggaran', function (Blueprint $table) {
            $table->dropColumn(['no_kontrak', 'pasal_pelanggaran', 'dasar_uu']);
        });
    }
};
