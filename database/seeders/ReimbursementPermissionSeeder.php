<?php

namespace Database\Seeders;

use App\Models\Permission_group;
use App\Models\ApprovalFeature;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class ReimbursementPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Permission Groups
        $groupJenis = Permission_group::firstOrCreate(['name' => 'Jenis Reimbursement']);
        $groupReimburse = Permission_group::firstOrCreate(['name' => 'Reimbursement']);

        // 2. Define Permissions
        $permissionsJenis = [
            'jenisreimbursement.index',
            'jenisreimbursement.create',
            'jenisreimbursement.edit',
            'jenisreimbursement.delete',
        ];

        $permissionsReimburse = [
            'reimbursement.index',
            'reimbursement.create',
            'reimbursement.edit',
            'reimbursement.delete',
            'reimbursement.approve',
            'reimbursement.pembayaran',
            'reimbursement.laporan',
        ];

        // 3. Create Permissions and assign to groups
        foreach ($permissionsJenis as $name) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['id_permission_group' => $groupJenis->id]
            );
        }

        foreach ($permissionsReimburse as $name) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['id_permission_group' => $groupReimburse->id]
            );
        }

        // 4. Assign to Super Admin (ID 1)
        $roleSuperAdmin = Role::where('name', 'super admin')->first();
        if ($roleSuperAdmin) {
            $allPermissions = array_merge($permissionsJenis, $permissionsReimburse);
            foreach ($allPermissions as $name) {
                $p = Permission::where('name', $name)->first();
                if ($p && !$roleSuperAdmin->hasPermissionTo($p)) {
                    $roleSuperAdmin->givePermissionTo($p);
                }
            }
        }

        // 5. Register Approval Feature
        ApprovalFeature::firstOrCreate(
            ['feature' => 'REIMBURSEMENT'],
            [
                'name' => 'Reimbursement',
                'description' => 'Konfigurasi persetujuan untuk modul pengajuan reimbursement karyawan.'
            ]
        );
    }
}
