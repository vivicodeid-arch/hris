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
        Schema::create('presensi_koreksi_approve', function (Blueprint $table) {
            $table->unsignedBigInteger('id_presensi');
            $table->char('kode_koreksi', 10);
            $table->timestamps();

            $table->foreign('id_presensi')->references('id')->on('presensi')->onDelete('cascade');
            $table->foreign('kode_koreksi')->references('kode_koreksi')->on('presensi_koreksi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_koreksi_approve');
    }
};
