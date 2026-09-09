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
        Schema::create('presensi_koreksi', function (Blueprint $table) {
            $table->char('kode_koreksi', 10)->primary();
            $table->char('nik', 9);
            $table->date('tanggal');
            $table->time('jam_in')->nullable();
            $table->time('jam_out')->nullable();
            $table->string('keterangan');
            $table->char('status', 1)->default('0'); // 0: pending, 1: approved, 2: rejected
            $table->integer('approval_step')->default(1);
            $table->unsignedBigInteger('id_user');
            $table->timestamps();

            $table->foreign('nik')->references('nik')->on('karyawan')->onDelete('cascade');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_koreksi');
    }
};
