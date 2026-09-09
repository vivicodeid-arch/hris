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
        Schema::create('reimbursement_karyawan', function (Blueprint $table) {
            $table->id();
            $table->char('nik', 10);
            $table->char('kode_jenis_reimburse', 5);
            $table->decimal('batas_nominal_override', 15, 2)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->foreign('nik')->references('nik')->on('karyawan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('kode_jenis_reimburse')->references('kode_jenis_reimburse')->on('jenis_reimbursement')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reimbursement_karyawan');
    }
};
