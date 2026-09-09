<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Performance optimization: Add composite indexes for tables frequently 
     * queried in the presensi_cetak/laporan report with large datasets.
     */
    public function up(): void
    {
        // Helper to check if index already exists
        $indexExists = function ($table, $indexName) {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        };

        // Composite index on presensi (nik, tanggal) - used in all report queries
        if (!$indexExists('presensi', 'idx_presensi_nik_tanggal')) {
            Schema::table('presensi', function (Blueprint $table) {
                $table->index(['nik', 'tanggal'], 'idx_presensi_nik_tanggal');
            });
        }

        // Index on hari_libur (tanggal) - used for holiday lookups
        if (!$indexExists('hari_libur', 'idx_hari_libur_tanggal')) {
            Schema::table('hari_libur', function (Blueprint $table) {
                $table->index('tanggal', 'idx_hari_libur_tanggal');
            });
        }

        // Composite index on presensi_jamkerja_bydate (nik, tanggal)
        if (!$indexExists('presensi_jamkerja_bydate', 'idx_jamkerja_bydate_nik_tanggal')) {
            Schema::table('presensi_jamkerja_bydate', function (Blueprint $table) {
                $table->index(['nik', 'tanggal'], 'idx_jamkerja_bydate_nik_tanggal');
            });
        }

        // Composite index on presensi_jamkerja_byday (nik, hari)
        if (!$indexExists('presensi_jamkerja_byday', 'idx_jamkerja_byday_nik_hari')) {
            Schema::table('presensi_jamkerja_byday', function (Blueprint $table) {
                $table->index(['nik', 'hari'], 'idx_jamkerja_byday_nik_hari');
            });
        }

        // Composite index on lembur (nik, tanggal) - used for overtime lookups
        if (!$indexExists('lembur', 'idx_lembur_nik_tanggal')) {
            Schema::table('lembur', function (Blueprint $table) {
                $table->index(['nik', 'tanggal'], 'idx_lembur_nik_tanggal');
            });
        }

        // Index on lembur_karyawan_khusus (nik, status) - used for special overtime rate lookup
        if (!$indexExists('lembur_karyawan_khusus', 'idx_lembur_khusus_nik_status')) {
            Schema::table('lembur_karyawan_khusus', function (Blueprint $table) {
                $table->index(['nik', 'status'], 'idx_lembur_khusus_nik_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexExists = function ($table, $indexName) {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        };

        if ($indexExists('presensi', 'idx_presensi_nik_tanggal')) {
            Schema::table('presensi', function (Blueprint $table) {
                $table->dropIndex('idx_presensi_nik_tanggal');
            });
        }

        if ($indexExists('hari_libur', 'idx_hari_libur_tanggal')) {
            Schema::table('hari_libur', function (Blueprint $table) {
                $table->dropIndex('idx_hari_libur_tanggal');
            });
        }

        if ($indexExists('presensi_jamkerja_bydate', 'idx_jamkerja_bydate_nik_tanggal')) {
            Schema::table('presensi_jamkerja_bydate', function (Blueprint $table) {
                $table->dropIndex('idx_jamkerja_bydate_nik_tanggal');
            });
        }

        if ($indexExists('presensi_jamkerja_byday', 'idx_jamkerja_byday_nik_hari')) {
            Schema::table('presensi_jamkerja_byday', function (Blueprint $table) {
                $table->dropIndex('idx_jamkerja_byday_nik_hari');
            });
        }

        if ($indexExists('lembur', 'idx_lembur_nik_tanggal')) {
            Schema::table('lembur', function (Blueprint $table) {
                $table->dropIndex('idx_lembur_nik_tanggal');
            });
        }

        if ($indexExists('lembur_karyawan_khusus', 'idx_lembur_khusus_nik_status')) {
            Schema::table('lembur_karyawan_khusus', function (Blueprint $table) {
                $table->dropIndex('idx_lembur_khusus_nik_status');
            });
        }
    }
};
