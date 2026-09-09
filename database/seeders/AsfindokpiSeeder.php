<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\KpiIndicator;
use App\Models\KpiIndicatorDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsfindokpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. Pastikan Jabatan Staff dengan Kode "05" exists
            $jabatanStaff = Jabatan::firstOrCreate(
                ['kode_jabatan' => '05'],
                ['nama_jabatan' => 'Staff']
            );

            // 1a. Pastikan Jabatan Kepala Divisi dengan Kode "02" exists
            $jabatanKepalaDivisi = Jabatan::firstOrCreate(
                ['kode_jabatan' => '02'],
                ['nama_jabatan' => 'Kepala Divisi']
            );

            // 1b. Pastikan Jabatan Wakil Divisi dengan Kode "03" exists
            $jabatanWakilDivisi = Jabatan::firstOrCreate(
                ['kode_jabatan' => '03'],
                ['nama_jabatan' => 'Wakil Divisi']
            );

            // 2. Pastikan Departemen Produksi dengan Kode "12" exists
            $deptProduksi = Departemen::firstOrCreate(
                ['kode_dept' => '12'],
                ['nama_dept' => 'Produksi']
            );

            // 3. Pastikan Departemen Packing dengan Kode "21" exists
            $deptPacking = Departemen::firstOrCreate(
                ['kode_dept' => '21'],
                ['nama_dept' => 'Packing']
            );

            // 3a. Pastikan Departemen Security dengan Kode "19" exists
            $deptSecurity = Departemen::firstOrCreate(
                ['kode_dept' => '19'],
                ['nama_dept' => 'SECURITY']
            );

            // 3b. Pastikan Departemen Office Boy dengan Kode "18" exists
            $deptOfficeBoy = Departemen::firstOrCreate(
                ['kode_dept' => '18'],
                ['nama_dept' => 'OFFICE BOY']
            );

            // 3c. Pastikan Departemen Inventori dengan Kode "10" exists
            $deptInventori = Departemen::firstOrCreate(
                ['kode_dept' => '10'],
                ['nama_dept' => 'INVENTORI']
            );

            // 3d. Pastikan Departemen Driver dengan Kode "13" exists
            $deptDriver = Departemen::firstOrCreate(
                ['kode_dept' => '13'],
                ['nama_dept' => 'DRIVER']
            );

            // 3e. Pastikan Departemen Purchasing dengan Kode "07" exists
            $deptPurchasing = Departemen::firstOrCreate(
                ['kode_dept' => '07'],
                ['nama_dept' => 'PURCHASING']
            );

            // 3f. Pastikan Departemen Quality Assurance dengan Kode "15" exists
            $deptQA = Departemen::firstOrCreate(
                ['kode_dept' => '15'],
                ['nama_dept' => 'QUALITY ASSURANCE']
            );

            // 4. Buat KPI Indicator Header untuk Staff Produksi
            $kpiStaffProduksi = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptProduksi->kode_dept
                ]
            );

            // 5. Buat KPI Indicator Header untuk Staff Packing
            $kpiStaffPacking = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptPacking->kode_dept
                ]
            );

            // 5a. Buat KPI Indicator Header untuk Staff Security
            $kpiStaffSecurity = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptSecurity->kode_dept
                ]
            );

            // 5b. Buat KPI Indicator Header untuk Staff Office Boy
            $kpiStaffOfficeBoy = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptOfficeBoy->kode_dept
                ]
            );

            // 5c. Buat KPI Indicator Header untuk Staff Inventori
            $kpiStaffInventori = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptInventori->kode_dept
                ]
            );

            // 5d. Buat KPI Indicator Header untuk Staff Driver
            $kpiStaffDriver = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptDriver->kode_dept
                ]
            );

            // 5e. Buat KPI Indicator Header untuk Kepala Divisi Produksi
            $kpiKepalaDivisiProduksi = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanKepalaDivisi->kode_jabatan,
                    'kode_dept' => $deptProduksi->kode_dept
                ]
            );

            // 5f. Buat KPI Indicator Header untuk Wakil Divisi Produksi
            $kpiWakilDivisiProduksi = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanWakilDivisi->kode_jabatan,
                    'kode_dept' => $deptProduksi->kode_dept
                ]
            );

            // 5g. Buat KPI Indicator Header untuk Kepala Divisi Packing
            $kpiKepalaDivisiPacking = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanKepalaDivisi->kode_jabatan,
                    'kode_dept' => $deptPacking->kode_dept
                ]
            );

            // 5h. Buat KPI Indicator Header untuk Wakil Divisi Packing
            $kpiWakilDivisiPacking = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanWakilDivisi->kode_jabatan,
                    'kode_dept' => $deptPacking->kode_dept
                ]
            );

            // 5i. Buat KPI Indicator Header untuk Kepala Divisi Purchasing
            $kpiKepalaDivisiPurchasing = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanKepalaDivisi->kode_jabatan,
                    'kode_dept' => $deptPurchasing->kode_dept
                ]
            );

            // 5j. Buat KPI Indicator Header untuk Wakil Divisi Purchasing
            $kpiWakilDivisiPurchasing = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanWakilDivisi->kode_jabatan,
                    'kode_dept' => $deptPurchasing->kode_dept
                ]
            );

            // 5k. Buat KPI Indicator Header untuk Kepala Divisi Quality Assurance
            $kpiKepalaDivisiQA = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanKepalaDivisi->kode_jabatan,
                    'kode_dept' => $deptQA->kode_dept
                ]
            );

            // 5l. Buat KPI Indicator Header untuk Wakil Divisi Quality Assurance
            $kpiWakilDivisiQA = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanWakilDivisi->kode_jabatan,
                    'kode_dept' => $deptQA->kode_dept
                ]
            );

            // 6. Indikator Detail untuk Staff Produksi
            $produksiIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 20,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Safety compliance',
                    'deskripsi' => 'Kepatuhan SOP & penggunaan APD',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Tanggung Jawab',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
            ];

            // 7. Indikator Detail untuk Staff Packing
            $packingIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Target Packing',
                    'deskripsi' => 'Jumlah paket / hari',
                    'satuan' => 'Paket',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 600.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 20,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Tanggung Jawab',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
            ];

            // 7a. Indikator Detail untuk Staff Security (Dept 19)
            $securityIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 10,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Safety compliance',
                    'deskripsi' => 'Kepatuhan SOP & penggunaan Seragam',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Jumlah kejadian keamanan',
                    'deskripsi' => 'Jumlah insiden keamanan (Target: 0 Incident)',
                    'satuan' => 'Incident',
                    'jenis_target' => 'min',
                    'bobot' => 15,
                    'target' => 0.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Pelaksanaan patroli',
                    'deskripsi' => 'Jumlah patroli sesuai jadwal (Target: >= 7 kali)',
                    'satuan' => 'Kali',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 7.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Pengawasan CCTV',
                    'deskripsi' => 'Ketepatan monitoring (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
            ];

            // 7b. Indikator Detail untuk Staff Office Boy (Dept 18)
            $officeBoyIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 20,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Safety compliance',
                    'deskripsi' => 'Kepatuhan SOP & penggunaan Seragam',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kebersihan Pabrik',
                    'deskripsi' => 'Penilaian kebersihan area pabrik (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kepedulian area kerja',
                    'deskripsi' => 'Proaktif menjaga kebersihan (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
            ];

            // 7c. Indikator Detail untuk Staff Inventori (Dept 10)
            $inventoriIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 20,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Akurasi Stock',
                    'deskripsi' => 'Selisih stock fisik vs sistem (Target >= 99%)',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 99.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Persentase Stock Hilang',
                    'deskripsi' => 'Kehilangan barang (Target <= 0.5%)',
                    'satuan' => 'Persen',
                    'jenis_target' => 'min',
                    'bobot' => 5,
                    'target' => 0.50,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kepatuhan SOP Gudang',
                    'deskripsi' => 'Mengikuti SOP (Target 100%)',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Ketepatan Laporan Stock',
                    'deskripsi' => 'Report harian/mingguan (Skala 1-5, Target: Sangat Baik / Tepat Waktu)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
            ];

            // 7d. Indikator Detail untuk Staff Driver (Dept 13)
            $driverIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 20,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Kepatuhan aturan lalu lintas',
                    'deskripsi' => 'Tilang / pelanggaran (Target: 0 pelanggaran)',
                    'satuan' => 'Pelanggaran',
                    'jenis_target' => 'min',
                    'bobot' => 10,
                    'target' => 0.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kondisi Kendaraan',
                    'deskripsi' => 'Penilaian kondisi kendaraan (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kelengkapan dokumen',
                    'deskripsi' => 'SIM / STNK / KIR (Target: 100% Lengkap)',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
            ];

            // 7e. Indikator Detail untuk Kepala Divisi Produksi (Dept 12)
            $kepalaDivisiProduksiIndicators = [
                [
                    'nama_indikator' => 'Output Produksi',
                    'deskripsi' => 'Jumlah produk selesai / Hari',
                    'satuan' => 'Pcs/Hari',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 45000.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Achievement produksi',
                    'deskripsi' => 'Realisasi vs target produksi',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 95.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Defect rate',
                    'deskripsi' => 'Persentase produk cacat',
                    'satuan' => 'Persen',
                    'jenis_target' => 'min',
                    'bobot' => 10,
                    'target' => 2.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Rework',
                    'deskripsi' => 'Jumlah produk yang harus diperbaiki',
                    'satuan' => 'Persen',
                    'jenis_target' => 'min',
                    'bobot' => 5,
                    'target' => 1.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Persentase kehadiran kerja',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 97.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah keterlambatan per bulan',
                    'satuan' => 'Kali',
                    'jenis_target' => 'min',
                    'bobot' => 5,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Safety compliance',
                    'deskripsi' => 'Kepatuhan SOP & penggunaan APD',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Downtime akibat operator',
                    'deskripsi' => 'Waktu mesin berhenti karena human error',
                    'satuan' => 'Menit',
                    'jenis_target' => 'min',
                    'bobot' => 5,
                    'target' => 0.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Audit Kebersihan area',
                    'deskripsi' => 'Nilai audit area kerja',
                    'satuan' => 'Nilai',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 90.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork & attitude',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Usulan perbaikan',
                    'deskripsi' => 'Jumlah ide improvement',
                    'satuan' => 'Ide',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 1.00,
                    'mode' => 'manual',
                ],
            ];

            // 7f. Indikator Detail untuk Wakil Divisi Produksi (Dept 12) - sama dengan Kepala Divisi
            $wakilDivisiProduksiIndicators = $kepalaDivisiProduksiIndicators;

            // 7g. Indikator Detail untuk Kepala Divisi Packing (Dept 21)
            $kepalaDivisiPackingIndicators = [
                [
                    'nama_indikator' => 'Output Packing',
                    'deskripsi' => 'Jumlah paket selesai / Hari',
                    'satuan' => 'Paket/Hari',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 23000.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Achievement packing',
                    'deskripsi' => 'Realisasi vs target packing',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 95.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Defect rate',
                    'deskripsi' => 'Persentase paket cacat',
                    'satuan' => 'Persen',
                    'jenis_target' => 'min',
                    'bobot' => 10,
                    'target' => 2.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Return',
                    'deskripsi' => 'Jumlah packing yang harus diperbaiki',
                    'satuan' => 'Persen',
                    'jenis_target' => 'min',
                    'bobot' => 5,
                    'target' => 1.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Persentase kehadiran kerja',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 97.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah keterlambatan per bulan',
                    'satuan' => 'Kali',
                    'jenis_target' => 'min',
                    'bobot' => 5,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Return akibat packing',
                    'deskripsi' => 'Return karena salah packing',
                    'satuan' => 'Target',
                    'jenis_target' => 'min',
                    'bobot' => 5,
                    'target' => 0.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'On-time packing',
                    'deskripsi' => 'Penyelesaian sesuai schedule',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 98.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Audit Kebersihan area',
                    'deskripsi' => 'Nilai audit area kerja',
                    'satuan' => 'Nilai',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 90.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork & attitude',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Usulan perbaikan',
                    'deskripsi' => 'Jumlah ide improvement',
                    'satuan' => 'Ide',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 1.00,
                    'mode' => 'manual',
                ],
            ];

            // 7h. Indikator Detail untuk Wakil Divisi Packing (Dept 21) - sama dengan Kepala Divisi Packing
            $wakilDivisiPackingIndicators = $kepalaDivisiPackingIndicators;

            // 7i. Indikator Detail untuk Kepala Divisi Purchasing (Dept 07)
            $kepalaDivisiPurchasingIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Persentase kehadiran kerja',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 97.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah keterlambatan per bulan',
                    'satuan' => 'Kali',
                    'jenis_target' => 'min',
                    'bobot' => 5,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Ketepatan waktu pembelian',
                    'deskripsi' => 'PR diselesaikan sesuai batas waktu yang sudah ditentukan',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 95.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Ketepatan Kedatangan Barang',
                    'deskripsi' => 'Barang diterima sesuai perkiraan waktu kedatangan yang diberikan oleh supplier',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 95.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Efisiensi Harga Pembelian',
                    'deskripsi' => 'Perbandingan harga supplier sebelumnya',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 3.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Akurasi Purchase Order (PO)',
                    'deskripsi' => 'Minim revisi/salah qty/salah item',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 99.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kelengkapan Dokumen Pembelian',
                    'deskripsi' => 'PO, invoice, surat jalan lengkap',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Persentase Barang Reject',
                    'deskripsi' => 'Barang rusak/tidak sesuai',
                    'satuan' => 'Persen',
                    'jenis_target' => 'min',
                    'bobot' => 10,
                    'target' => 2.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Audit Kebersihan area',
                    'deskripsi' => 'Nilai audit area kerja',
                    'satuan' => 'Nilai',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 90.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork & attitude',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Usulan perbaikan',
                    'deskripsi' => 'Jumlah ide improvement',
                    'satuan' => 'Ide',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 1.00,
                    'mode' => 'manual',
                ],
            ];

            // 7j. Indikator Detail untuk Wakil Divisi Purchasing (Dept 07) - sama dengan Kepala Divisi Purchasing
            $wakilDivisiPurchasingIndicators = $kepalaDivisiPurchasingIndicators;

            // 7k. Indikator Detail untuk Kepala Divisi Quality Assurance (Dept 15)
            $kepalaDivisiQAIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Persentase kehadiran kerja',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 97.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah keterlambatan per bulan',
                    'satuan' => 'Kali',
                    'jenis_target' => 'min',
                    'bobot' => 5,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Akurasi Dokumentasi Batch Record',
                    'deskripsi' => 'Tidak ada data salah data/missing',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 99.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Ketepatan Investigasi Masalah',
                    'deskripsi' => 'Proses menangani dan memperbaiki penyimpangan atau ketidaksesuaian dari target, standar, prosedur, atau rencana',
                    'satuan' => 'Jam',
                    'jenis_target' => 'min',
                    'bobot' => 10,
                    'target' => 24.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Jumlah keluhan konsumen',
                    'deskripsi' => 'Keluhan masuk mengenai kualitas produk (per 1.000 pcs produk terjual)',
                    'satuan' => 'KM',
                    'jenis_target' => 'min',
                    'bobot' => 10,
                    'target' => 50.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Ketepatan FIFO',
                    'deskripsi' => 'Barang keluar sesuai expired',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Audit Kebersihan area',
                    'deskripsi' => 'Nilai audit area kerja',
                    'satuan' => 'Nilai',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 90.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork & attitude',
                    'deskripsi' => 'Penilaian Management',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Usulan perbaikan',
                    'deskripsi' => 'Jumlah ide improvement',
                    'satuan' => 'Ide',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 1.00,
                    'mode' => 'manual',
                ],
            ];

            // 7l. Indikator Detail untuk Wakil Divisi Quality Assurance (Dept 15) - sama dengan Kepala Divisi QA
            $wakilDivisiQAIndicators = $kepalaDivisiQAIndicators;

            // Sync Produksi Indicators
            $produksiNames = array_column($produksiIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffProduksi->id)
                ->whereNotIn('nama_indikator', $produksiNames)
                ->delete();

            foreach ($produksiIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffProduksi->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Packing Indicators
            $packingNames = array_column($packingIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffPacking->id)
                ->whereNotIn('nama_indikator', $packingNames)
                ->delete();

            foreach ($packingIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffPacking->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Security Indicators
            $securityNames = array_column($securityIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffSecurity->id)
                ->whereNotIn('nama_indikator', $securityNames)
                ->delete();

            foreach ($securityIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffSecurity->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Office Boy Indicators
            $officeBoyNames = array_column($officeBoyIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffOfficeBoy->id)
                ->whereNotIn('nama_indikator', $officeBoyNames)
                ->delete();

            foreach ($officeBoyIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffOfficeBoy->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Inventori Indicators
            $inventoriNames = array_column($inventoriIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffInventori->id)
                ->whereNotIn('nama_indikator', $inventoriNames)
                ->delete();

            foreach ($inventoriIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffInventori->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Driver Indicators
            $driverNames = array_column($driverIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffDriver->id)
                ->whereNotIn('nama_indikator', $driverNames)
                ->delete();

            foreach ($driverIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffDriver->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Kepala Divisi Produksi Indicators
            $kepalaDivisiProduksiNames = array_column($kepalaDivisiProduksiIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiKepalaDivisiProduksi->id)
                ->whereNotIn('nama_indikator', $kepalaDivisiProduksiNames)
                ->delete();

            foreach ($kepalaDivisiProduksiIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiKepalaDivisiProduksi->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Wakil Divisi Produksi Indicators
            $wakilDivisiProduksiNames = array_column($wakilDivisiProduksiIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiWakilDivisiProduksi->id)
                ->whereNotIn('nama_indikator', $wakilDivisiProduksiNames)
                ->delete();

            foreach ($wakilDivisiProduksiIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiWakilDivisiProduksi->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Kepala Divisi Packing Indicators
            $kepalaDivisiPackingNames = array_column($kepalaDivisiPackingIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiKepalaDivisiPacking->id)
                ->whereNotIn('nama_indikator', $kepalaDivisiPackingNames)
                ->delete();

            foreach ($kepalaDivisiPackingIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiKepalaDivisiPacking->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Wakil Divisi Packing Indicators
            $wakilDivisiPackingNames = array_column($wakilDivisiPackingIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiWakilDivisiPacking->id)
                ->whereNotIn('nama_indikator', $wakilDivisiPackingNames)
                ->delete();

            foreach ($wakilDivisiPackingIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiWakilDivisiPacking->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Kepala Divisi Purchasing Indicators
            $kepalaDivisiPurchasingNames = array_column($kepalaDivisiPurchasingIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiKepalaDivisiPurchasing->id)
                ->whereNotIn('nama_indikator', $kepalaDivisiPurchasingNames)
                ->delete();

            foreach ($kepalaDivisiPurchasingIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiKepalaDivisiPurchasing->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Wakil Divisi Purchasing Indicators
            $wakilDivisiPurchasingNames = array_column($wakilDivisiPurchasingIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiWakilDivisiPurchasing->id)
                ->whereNotIn('nama_indikator', $wakilDivisiPurchasingNames)
                ->delete();

            foreach ($wakilDivisiPurchasingIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiWakilDivisiPurchasing->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Kepala Divisi QA Indicators
            $kepalaDivisiQANames = array_column($kepalaDivisiQAIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiKepalaDivisiQA->id)
                ->whereNotIn('nama_indikator', $kepalaDivisiQANames)
                ->delete();

            foreach ($kepalaDivisiQAIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiKepalaDivisiQA->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Wakil Divisi QA Indicators
            $wakilDivisiQANames = array_column($wakilDivisiQAIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiWakilDivisiQA->id)
                ->whereNotIn('nama_indikator', $wakilDivisiQANames)
                ->delete();

            foreach ($wakilDivisiQAIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiWakilDivisiQA->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            DB::commit();
            $this->command->info('KPI Asfindo Seeder ran successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error running KPI Asfindo Seeder: ' . $e->getMessage());
        }
    }
}
