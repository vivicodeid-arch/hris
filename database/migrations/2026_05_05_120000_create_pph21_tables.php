<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel setting global PPh 21
        Schema::create('pph21_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('status_aktif')->default(false)->comment('Toggle aktif/nonaktif fitur PPh 21');
            $table->enum('metode', ['TER', 'PROGRESIF'])->default('TER')->comment('Metode perhitungan: TER atau Progresif manual');
            $table->enum('metode_tanggungan', ['GROSS', 'GROSS_UP'])->default('GROSS')->comment('GROSS=karyawan tanggung PPh, GROSS_UP=perusahaan tanggung');
            $table->decimal('biaya_jabatan_persen', 5, 2)->default(5.00)->comment('% Biaya Jabatan (default 5%)');
            $table->bigInteger('biaya_jabatan_max_bulan')->default(500000)->comment('Max biaya jabatan per bulan (default 500rb)');
            $table->timestamps();
        });

        // 2. Tabel komponen formula (yg bisa dikustom)
        Schema::create('pph21_formula_komponen', function (Blueprint $table) {
            $table->id();
            $table->string('nama_komponen', 100)->comment('Label tampilan komponen');
            $table->enum('tipe', ['penambah', 'pengurang'])->comment('penambah=masuk bruto, pengurang=dikurangi dari bruto');
            $table->enum('sumber', ['gaji_pokok', 'tunjangan', 'bpjs_kesehatan', 'bpjs_tenagakerja', 'lembur'])->comment('Sumber data komponen');
            $table->string('kode_sumber', 10)->nullable()->comment('Kode jenis tunjangan jika sumber=tunjangan');
            $table->boolean('status_aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // 3. Tambah kolom kategori_ter ke tabel status_kawin
        Schema::table('status_kawin', function (Blueprint $table) {
            $table->char('kategori_ter', 1)->nullable()->after('status_kawin')->comment('Kategori TER: A, B, atau C');
            $table->bigInteger('nilai_ptkp')->default(54000000)->after('kategori_ter')->comment('Nilai PTKP per tahun dalam rupiah');
        });

        // 4. Tabel tarif TER bulanan (PP 58/2023)
        Schema::create('pph21_ter_rates', function (Blueprint $table) {
            $table->id();
            $table->char('kategori', 1)->comment('A, B, atau C');
            $table->bigInteger('penghasilan_dari')->default(0)->comment('Batas bawah penghasilan bruto bulanan');
            $table->bigInteger('penghasilan_sampai')->nullable()->comment('Batas atas (null = tak terbatas)');
            $table->decimal('tarif_persen', 5, 2)->default(0.00)->comment('Tarif TER dalam persen');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        // 5. Tabel tarif progresif Pasal 17 UU HPP
        Schema::create('pph21_progresif_rates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pkp_dari')->default(0)->comment('Batas bawah PKP setahun');
            $table->bigInteger('pkp_sampai')->nullable()->comment('Batas atas PKP setahun (null = tak terbatas)');
            $table->decimal('tarif_persen', 5, 2)->default(0.00)->comment('Tarif dalam persen');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        // 6. Tabel snapshot PPh 21 per karyawan per slip (tanpa FK supaya fleksibel)
        Schema::create('pph21_slip_detail', function (Blueprint $table) {
            $table->id();
            $table->char('kode_slip_gaji', 8)->index()->comment('Ref ke slip_gaji');
            $table->char('nik', 10)->comment('NIK karyawan');
            $table->char('kode_status_kawin', 5)->nullable()->comment('Snapshot status kawin saat generate');
            $table->char('kategori_ter', 1)->nullable()->comment('A/B/C');
            $table->enum('metode', ['TER', 'PROGRESIF'])->default('TER');
            $table->enum('metode_tanggungan', ['GROSS', 'GROSS_UP'])->default('GROSS');
            $table->bigInteger('penghasilan_bruto')->default(0)->comment('Total bruto sesuai formula');
            $table->bigInteger('biaya_jabatan')->default(0);
            $table->bigInteger('nilai_ptkp')->default(0)->comment('Nilai PTKP per tahun');
            $table->bigInteger('pkp_setahun')->default(0)->comment('Penghasilan Kena Pajak setahun');
            $table->decimal('tarif_ter_persen', 5, 2)->default(0)->comment('Tarif TER yg digunakan');
            $table->bigInteger('pph21_terutang')->default(0)->comment('PPh 21 final bulan ini');
            $table->bigInteger('pph21_ditanggung_perusahaan')->default(0)->comment('Jika gross-up');
            $table->json('detail_komponen')->nullable()->comment('Snapshot rincian komponen bruto');
            $table->timestamps();

            $table->unique(['kode_slip_gaji', 'nik']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pph21_slip_detail');
        Schema::dropIfExists('pph21_progresif_rates');
        Schema::dropIfExists('pph21_ter_rates');
        Schema::table('status_kawin', function (Blueprint $table) {
            $table->dropColumn(['kategori_ter', 'nilai_ptkp']);
        });
        Schema::dropIfExists('pph21_formula_komponen');
        Schema::dropIfExists('pph21_settings');
    }
};
