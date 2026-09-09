<?php

namespace Database\Seeders;

use App\Models\Denda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DendaAturanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel denda terlebih dahulu agar tidak duplikat
        DB::table('denda')->truncate();

        $tolerance = 10; // Menit toleransi
        $ratePerMinute = 1000; // Denda per menit
        $maxMinutes = 60; // Batas akhir denda (misal 4 jam)

        $data = [];

        // Denda dimulai dari menit ke-11
        for ($i = $tolerance + 1; $i <= $maxMinutes; $i++) {
            // Hitung denda: (menit terlambat - toleransi) * rate
            // Contoh: 11 menit -> (11-10) * 1000 = 1000
            $jumlahDenda = ($i - $tolerance) * $ratePerMinute;

            $data[] = [
                'dari' => $i,
                'sampai' => $i,
                'denda' => $jumlahDenda,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert per 100 baris agar lebih cepat dan tidak membebani memori
            if (count($data) >= 100) {
                DB::table('denda')->insert($data);
                $data = [];
            }
        }

        // Insert sisa data jika ada
        if (count($data) > 0) {
            DB::table('denda')->insert($data);
        }

        $this->command->info("Seeder Aturan Denda berhasil dijalankan.");
        $this->command->info("Aturan: Toleransi {$tolerance} menit, Denda {$ratePerMinute}/menit (Mulai menit ke-11).");
    }
}
