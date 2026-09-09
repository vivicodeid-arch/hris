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
        Schema::create('jenis_reimbursement', function (Blueprint $table) {
            $table->id();
            $table->char('kode_jenis_reimburse', 5)->unique();
            $table->string('nama_jenis', 100);
            $table->text('deskripsi')->nullable();
            $table->decimal('batas_nominal', 15, 2);
            $table->decimal('batas_nominal_bulanan', 15, 2)->nullable();
            $table->decimal('batas_nominal_tahunan', 15, 2)->nullable();
            $table->tinyInteger('wajib_bukti')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_reimbursement');
    }
};
