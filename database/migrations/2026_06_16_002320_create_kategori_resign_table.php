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
        Schema::create('kategori_resign', function (Blueprint $table) {
            $table->string('kode_kategori', 5)->primary();
            $table->string('nama_kategori', 50);
            $table->timestamps();
        });

        // Insert initial categories
        \Illuminate\Support\Facades\DB::table('kategori_resign')->insert([
            ['kode_kategori' => 'KR001', 'nama_kategori' => 'Mengundurkan Diri', 'created_at' => now(), 'updated_at' => now()],
            ['kode_kategori' => 'KR002', 'nama_kategori' => 'PHK', 'created_at' => now(), 'updated_at' => now()],
            ['kode_kategori' => 'KR003', 'nama_kategori' => 'Pensiun', 'created_at' => now(), 'updated_at' => now()],
            ['kode_kategori' => 'KR004', 'nama_kategori' => 'Kontrak Berakhir', 'created_at' => now(), 'updated_at' => now()],
            ['kode_kategori' => 'KR005', 'nama_kategori' => 'Meninggal Dunia', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('resign_karyawans', function (Blueprint $table) {
            $table->dropColumn('kategori');
            $table->string('kode_kategori', 5)->nullable()->after('tanggal_resign');
            
            $table->foreign('kode_kategori')->references('kode_kategori')->on('kategori_resign')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resign_karyawans', function (Blueprint $table) {
            $table->dropForeign(['kode_kategori']);
            $table->dropColumn('kode_kategori');
            $table->string('kategori', 50)->nullable()->after('tanggal_resign');
        });

        Schema::dropIfExists('kategori_resign');
    }
};
