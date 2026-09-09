<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([




                // Settings & Features
                //Defaultsettingseeder::class,
                //ApprovalFeatureSeeder::class,
                //ApprovalLayerSeeder::class,

                // Permissions - Groups & Roles Base
            Gruppermissionseeder::class,
            GrupDetailPermissionSeeder::class,
            GrupSetJamKerjaPermissionSeeder::class,

                // Permissions - Alphabetical
            MutasiPermissionSeeder::class,
            AktivitasKaryawanPermissionSeeder::class,
            AktivitasKaryawanKaryawanPermissionSeeder::class,
            ApprovalLayerPermissionSeeder::class,
            Bersihkanfotopermissionseeder::class,
            Bpjskesehatanpermissionseeder::class,
            Bpjstenagakerjapermissionseeder::class,
            Gajipokokpermissionsseeder::class,
            Hariliburpermissionseeder::class,
            Izinabsenpermissionseeder::class,
            Izincutipermissionseeder::class,
            Izindinaspermissionseeder::class,
            Izinsakitpermissionseeder::class,
            Jabatanpermissionseeder::class,
            Jamkerjabydeptpermissionseeder::class,
            Jamkerjapermissionseeder::class,
            Jenistunjanganpermissionseeder::class,
            KaryawanSetCabangPermissionSeeder::class,
            KontrakKaryawanPermissionSeeder::class,
            Kontrakpermissionseeder::class,
            KunjunganPermissionSeeder::class,
            Laporanpermissionseeder::class,
            LaporanCutiPermissionSeeder::class,
            Lemburpermissionseeder::class,
            PelanggaranPermissionSeeder::class,
            Pengaturanumumpermissionseeder::class,
            PengumumanPermissionSeeder::class,
            Penyesuaiangajipermissionseeder::class,
            Koreksipermissionseeder::class,
            Presensipermissionseeder::class,
            Slipgajipermissionseeder::class,
            Slipgajikaryawanpermissionseeder::class,
            Trackingpresensipermissionseeder::class,
            Tunjanganpermissionseeder::class,
            Wagatewaypermissionseeder::class,
            PermissionKpiSeeder::class,
            KpiPermissionSeeder::class,
            AjuanJadwalPermissionSeeder::class,
            LaporanJadwalPermissionSeeder::class,
            MesinFingerprintPermissionSeeder::class,
            DendaPermissionSeeder::class,
            PinjamanPermissionSeeder::class,
            UserLoginLogPermissionSeeder::class,
            StatusKaryawanSeeder::class,
            StatusKawinPermissionSeeder::class,
            StatusKaryawanPermissionSeeder::class,
            BackupPermissionSeeder::class,
            ReimbursementPermissionSeeder::class,
            LogMesinPermissionSeeder::class,
            Pph21Seeder::class,
            Pph21PermissionSeeder::class,
            KaryawanMenuSettingSeeder::class,
                //DendaAturanSeeder::class,



                // Specific Assignments
            KategoriResignPermissionSeeder::class,
            givePermissionizindinastokaryawanseeder::class,
            AdminUserSeeder::class,
            MasterAdminSeeder::class,

            // Dummy & Transactional Data
            //DummyDataSeeder::class,
            //KunjunganSeeder::class,
            //AsfindokpiSeeder::class,
        ]);
    }
}
