<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Pph21Seeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // 1. Setting default PPh 21
        // ============================================================
        DB::table('pph21_settings')->updateOrInsert(['id' => 1], [
            'status_aktif'            => false,
            'metode'                  => 'TER',
            'metode_tanggungan'       => 'GROSS',
            'biaya_jabatan_persen'    => 5.00,
            'biaya_jabatan_max_bulan' => 500000,
            'created_at'              => now(),
            'updated_at'              => now(),
        ]);

        // ============================================================
        // 2. Update kategori_ter & nilai_ptkp pada tabel status_kawin
        //    Sesuai PMK 101/PMK.010/2016 & PP 58/2023
        // ============================================================
        // Mapping disesuaikan dengan kode status kawin yang ada di database
        // TK  = Tidak Kawin                      → TER A, PTKP 54 jt (setara TK/0)
        // K0  = Kawin Belum Punya Tanggungan     → TER A, PTKP 58,5 jt (setara K/0)
        // K1  = Kawin Punya Tanggungan 1         → TER B, PTKP 63 jt   (setara K/1)
        // K2  = Kawin Punya Tanggungan 2         → TER B, PTKP 67,5 jt (setara K/2)
        // K3  = Kawin Punya Tanggungan 3         → TER C, PTKP 72 jt   (setara K/3)
        // HB  = Janda/Duda                       → TER A, PTKP 54 jt   (setara TK/0)
        $ptkpMapping = [
            'TK' => ['kategori_ter' => 'A', 'nilai_ptkp' => 54000000],
            'K0' => ['kategori_ter' => 'A', 'nilai_ptkp' => 58500000],
            'K1' => ['kategori_ter' => 'B', 'nilai_ptkp' => 63000000],
            'K2' => ['kategori_ter' => 'B', 'nilai_ptkp' => 67500000],
            'K3' => ['kategori_ter' => 'C', 'nilai_ptkp' => 72000000],
            'HB' => ['kategori_ter' => 'A', 'nilai_ptkp' => 54000000],
        ];

        foreach ($ptkpMapping as $kode => $data) {
            DB::table('status_kawin')
                ->where('kode_status_kawin', $kode)
                ->update([
                    'kategori_ter' => $data['kategori_ter'],
                    'nilai_ptkp'   => $data['nilai_ptkp'],
                ]);
        }

        // ============================================================
        // 3. Komponen formula default
        // ============================================================
        DB::table('pph21_formula_komponen')->truncate();
        DB::table('pph21_formula_komponen')->insert([
            [
                'nama_komponen' => 'Gaji Pokok',
                'tipe'          => 'penambah',
                'sumber'        => 'gaji_pokok',
                'kode_sumber'   => null,
                'status_aktif'  => true,
                'urutan'        => 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nama_komponen' => 'Semua Tunjangan',
                'tipe'          => 'penambah',
                'sumber'        => 'tunjangan',
                'kode_sumber'   => null,
                'status_aktif'  => true,
                'urutan'        => 2,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nama_komponen' => 'BPJS Tenaga Kerja (Karyawan)',
                'tipe'          => 'pengurang',
                'sumber'        => 'bpjs_tenagakerja',
                'kode_sumber'   => null,
                'status_aktif'  => false, // nonaktif default, user aktifkan jika perlu
                'urutan'        => 3,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);

        // ============================================================
        // 4. Tabel TER Bulanan (PP 58/2023) — Kategori A, B, C
        //    Sumber: Lampiran PP No. 58 Tahun 2023
        // ============================================================
        DB::table('pph21_ter_rates')->truncate();

        $terRates = [];

        // --- Kategori A (TK/0, TK/1, K/0) ---
        $kategoriA = [
            [0,          5400000,    0.00],
            [5400001,    5650000,    0.25],
            [5650001,    5950000,    0.50],
            [5950001,    6300000,    0.75],
            [6300001,    6750000,    1.00],
            [6750001,    7500000,    1.25],
            [7500001,    8550000,    1.50],
            [8550001,    9650000,    2.00],
            [9650001,    10050000,   2.50],
            [10050001,   10350000,   3.00],
            [10350001,   10700000,   3.50],
            [10700001,   11050000,   4.00],
            [11050001,   11600000,   4.50],
            [11600001,   12500000,   5.00],
            [12500001,   13750000,   5.50],
            [13750001,   15100000,   6.00],
            [15100001,   16950000,   7.00],
            [16950001,   19750000,   8.00],
            [19750001,   24150000,   9.00],
            [24150001,   26450000,   10.00],
            [26450001,   28000000,   11.00],
            [28000001,   30050000,   12.00],
            [30050001,   32400000,   13.00],
            [32400001,   35400000,   14.00],
            [35400001,   39100000,   15.00],
            [39100001,   43850000,   16.00],
            [43850001,   47800000,   17.00],
            [47800001,   51400000,   17.50],
            [51400001,   56300000,   18.00],
            [56300001,   62200000,   18.50],
            [62200001,   68600000,   19.00],
            [68600001,   77500000,   19.50],
            [77500001,   89000000,   20.00],
            [89000001,   103000000,  21.00],
            [103000001,  125000000,  22.00],
            [125000001,  157000000,  23.00],
            [157000001,  206000000,  24.00],
            [206000001,  337000000,  25.00],
            [337000001,  454000000,  26.00],
            [454000001,  550000000,  27.00],
            [550000001,  695000000,  28.00],
            [695000001,  910000000,  29.00],
            [910000001,  1400000000, 30.00],
            [1400000001, null,       34.00],
        ];

        foreach ($kategoriA as $r) {
            $terRates[] = [
                'kategori'           => 'A',
                'penghasilan_dari'   => $r[0],
                'penghasilan_sampai' => $r[2] === 34.00 ? null : $r[1],
                'tarif_persen'       => $r[2],
                'status_aktif'       => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        // --- Kategori B (TK/2, TK/3, K/1, K/2) ---
        $kategoriB = [
            [0,          6200000,    0.00],
            [6200001,    6500000,    0.25],
            [6500001,    6850000,    0.50],
            [6850001,    7300000,    0.75],
            [7300001,    9200000,    1.00],
            [9200001,    10750000,   1.50],
            [10750001,   11250000,   2.00],
            [11250001,   11600000,   2.50],
            [11600001,   12600000,   3.00],
            [12600001,   13600000,   3.50],
            [13600001,   14950000,   4.00],
            [14950001,   16400000,   4.50],
            [16400001,   18450000,   5.00],
            [18450001,   21850000,   5.50],
            [21850001,   26000000,   6.00],
            [26000001,   27700000,   7.00],
            [27700001,   29350000,   8.00],
            [29350001,   31450000,   9.00],
            [31450001,   33950000,   10.00],
            [33950001,   37100000,   11.00],
            [37100001,   41100000,   12.00],
            [41100001,   45800000,   13.00],
            [45800001,   51400000,   14.00],
            [51400001,   58500000,   15.00],
            [58500001,   64000000,   16.00],
            [64000001,   71000000,   17.00],
            [71000001,   80000000,   18.00],
            [80000001,   93000000,   19.00],
            [93000001,   109000000,  20.00],
            [109000001,  129000000,  21.00],
            [129000001,  163000000,  22.00],
            [163000001,  211000000,  23.00],
            [211000001,  374000000,  24.00],
            [374000001,  459000000,  25.00],
            [459000001,  555000000,  26.00],
            [555000001,  704000000,  27.00],
            [704000001,  957000000,  28.00],
            [957000001,  1405000000, 29.00],
            [1405000001, null,       34.00],
        ];

        foreach ($kategoriB as $r) {
            $terRates[] = [
                'kategori'           => 'B',
                'penghasilan_dari'   => $r[0],
                'penghasilan_sampai' => $r[2] === 34.00 ? null : $r[1],
                'tarif_persen'       => $r[2],
                'status_aktif'       => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        // --- Kategori C (K/3) ---
        $kategoriC = [
            [0,          6600000,    0.00],
            [6600001,    6950000,    0.25],
            [6950001,    7350000,    0.50],
            [7350001,    7800000,    0.75],
            [7800001,    8850000,    1.00],
            [8850001,    9800000,    1.25],
            [9800001,    10950000,   1.50],
            [10950001,   11200000,   2.00],
            [11200001,   12050000,   3.00],
            [12050001,   12950000,   3.50],
            [12950001,   14150000,   4.00],
            [14150001,   15550000,   4.50],
            [15550001,   17050000,   5.00],
            [17050001,   19500000,   5.50],
            [19500001,   22700000,   6.00],
            [22700001,   29500000,   7.00],
            [29500001,   33950000,   8.00],
            [33950001,   37000000,   9.00],
            [37000001,   41000000,   10.00],
            [41000001,   44550000,   11.00],
            [44550001,   50000000,   12.00],
            [50000001,   56500000,   13.00],
            [56500001,   63000000,   14.00],
            [63000001,   70000000,   15.00],
            [70000001,   80000000,   16.00],
            [80000001,   96000000,   17.00],
            [96000001,   110000000,  18.00],
            [110000001,  134000000,  19.00],
            [134000001,  160000000,  20.00],
            [160000001,  198000000,  21.00],
            [198000001,  255000000,  22.00],
            [255000001,  330000000,  23.00],
            [330000001,  430000000,  24.00],
            [430000001,  555000000,  25.00],
            [555000001,  704000000,  26.00],
            [704000001,  957000000,  27.00],
            [957000001,  1405000000, 28.00],
            [1405000001, null,       34.00],
        ];

        foreach ($kategoriC as $r) {
            $terRates[] = [
                'kategori'           => 'C',
                'penghasilan_dari'   => $r[0],
                'penghasilan_sampai' => $r[2] === 34.00 ? null : $r[1],
                'tarif_persen'       => $r[2],
                'status_aktif'       => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        DB::table('pph21_ter_rates')->insert($terRates);

        // ============================================================
        // 5. Tarif Progresif Pasal 17 UU HPP
        // ============================================================
        DB::table('pph21_progresif_rates')->truncate();
        DB::table('pph21_progresif_rates')->insert([
            ['pkp_dari' => 0,            'pkp_sampai' => 60000000,     'tarif_persen' => 5,  'status_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['pkp_dari' => 60000000,     'pkp_sampai' => 250000000,    'tarif_persen' => 15, 'status_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['pkp_dari' => 250000000,    'pkp_sampai' => 500000000,    'tarif_persen' => 25, 'status_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['pkp_dari' => 500000000,    'pkp_sampai' => 5000000000,   'tarif_persen' => 30, 'status_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['pkp_dari' => 5000000000,   'pkp_sampai' => null,         'tarif_persen' => 35, 'status_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->command->info('✅ Pph21Seeder berhasil dijalankan.');
        $this->command->info('   - Setting PPh 21 default (nonaktif)');
        $this->command->info('   - Kategori TER & nilai PTKP diupdate ke status_kawin');
        $this->command->info('   - Formula komponen default: Gaji Pokok + Tunjangan');
        $this->command->info('   - ' . count($terRates) . ' baris tarif TER (Kategori A/B/C)');
        $this->command->info('   - 5 lapisan tarif progresif Pasal 17 UU HPP');
    }
}
