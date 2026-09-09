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
        Schema::create('reimbursement_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reimbursement_id');
            $table->char('kode_jenis_reimburse', 5);
            $table->date('tanggal_transaksi');
            $table->text('keterangan');
            $table->decimal('nominal', 15, 2);
            $table->decimal('nominal_disetujui', 15, 2)->nullable();
            $table->string('bukti_file')->nullable();
            $table->timestamps();

            $table->foreign('reimbursement_id')->references('id')->on('reimbursement')->onDelete('cascade');
            $table->foreign('kode_jenis_reimburse')->references('kode_jenis_reimburse')->on('jenis_reimbursement')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reimbursement_detail');
    }
};
