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
        Schema::create('karyawan_pelatihan', function (Blueprint $table) {
            $table->id();
            $table->char('nik', 9)->index();
            $table->string('nama_pelatihan', 150);
            $table->string('penyelenggara', 150);
            $table->date('tanggal_pelatihan');
            $table->string('foto', 255)->nullable();
            $table->timestamps();
            
            $table->foreign('nik')->references('nik')->on('karyawan')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan_pelatihan');
    }
};
