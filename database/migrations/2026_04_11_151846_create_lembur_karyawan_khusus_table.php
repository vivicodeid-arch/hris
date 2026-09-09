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
        Schema::create('lembur_karyawan_khusus', function (Blueprint $table) {
            $table->id();
            $table->char('nik', 10);
            $table->bigInteger('upah_perjam');
            $table->text('keterangan')->nullable();
            $table->boolean('status')->default(true);
            $table->foreign('nik')->references('nik')->on('karyawan')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lembur_karyawan_khusus');
    }
};
