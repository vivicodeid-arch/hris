<?php

namespace Database\Seeders;

use App\Models\KaryawanMenuSetting;
use Illuminate\Database\Seeder;

class KaryawanMenuSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            ['kode_menu' => 'idcard', 'nama_menu' => 'ID Card', 'status' => 1],
            ['kode_menu' => 'absen_istirahat', 'nama_menu' => 'Absen Istirahat', 'status' => 1],
            ['kode_menu' => 'kontrak', 'nama_menu' => 'Dokumen Kontrak', 'status' => 1],
            ['kode_menu' => 'lembur', 'nama_menu' => 'Lembur Harian', 'status' => 1],
            ['kode_menu' => 'slipgaji', 'nama_menu' => 'Slip Gaji', 'status' => 1],
            ['kode_menu' => 'aktivitas', 'nama_menu' => 'Aktivitas', 'status' => 1],
            ['kode_menu' => 'visit', 'nama_menu' => 'Kunjungan / Visit', 'status' => 1],
            ['kode_menu' => 'wajah', 'nama_menu' => 'Daftar Face ID', 'status' => 1],
            ['kode_menu' => 'pelanggaran', 'nama_menu' => 'Pelanggaran (SP)', 'status' => 1],
            ['kode_menu' => 'pinjaman', 'nama_menu' => 'Pinjaman (PJP)', 'status' => 1],
            ['kode_menu' => 'reimbursement', 'nama_menu' => 'Reimbursement', 'status' => 1],
            ['kode_menu' => 'jadwal_saya', 'nama_menu' => 'Jadwal Saya', 'status' => 1],
            ['kode_menu' => 'tukar_shift', 'nama_menu' => 'Tukar Shift', 'status' => 1],
            ['kode_menu' => 'kpi', 'nama_menu' => 'Penilaian KPI', 'status' => 1],
            ['kode_menu' => 'project_board', 'nama_menu' => 'Project Board', 'status' => 1],
            ['kode_menu' => 'pengumuman', 'nama_menu' => 'Pengumuman', 'status' => 1],
            ['kode_menu' => 'hak_approval', 'nama_menu' => 'Hak Approval', 'status' => 1],
        ];

        foreach ($menus as $menu) {
            KaryawanMenuSetting::updateOrCreate(
                ['kode_menu' => $menu['kode_menu']],
                [
                    'nama_menu' => $menu['nama_menu'],
                    'status' => $menu['status']
                ]
            );
        }
    }
}
