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
        Schema::create('reimbursement', function (Blueprint $table) {
            $table->id();
            $table->string('no_reimbursement', 20)->unique();
            $table->char('nik', 10);
            $table->date('tanggal_pengajuan');
            $table->decimal('total_nominal', 15, 2);
            $table->decimal('total_disetujui', 15, 2)->default(0);
            $table->char('status', 1)->default('P'); // P=Pending, A=Approved, R=Rejected, D=Dibayar, B=Batal
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->string('metode_pembayaran', 50)->nullable();
            $table->string('bukti_pembayaran')->nullable();
            $table->integer('approval_step')->default(1);
            $table->timestamps();

            $table->foreign('nik')->references('nik')->on('karyawan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reimbursement');
    }
};
