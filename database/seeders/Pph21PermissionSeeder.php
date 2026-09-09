<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Pph21PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Permission Group PPh 21 jika belum ada
        $groupName = 'PPh 21';
        $groupId = DB::table('permission_groups')->where('name', $groupName)->value('id');

        if (!$groupId) {
            $groupId = DB::table('permission_groups')->insertGetId([
                'name' => $groupName,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->command->info("✅ Group Permission '{$groupName}' berhasil dibuat (ID: {$groupId}).");
        }

        // 2. Buat permission pph21.index
        $permission = Permission::updateOrCreate(
            ['name' => 'pph21.index'],
            [
                'guard_name' => 'web',
                'id_permission_group' => $groupId
            ]
        );

        // 3. Berikan akses ke Super Admin
        $role = Role::where('name', 'super admin')->first();
        if ($role) {
            $role->givePermissionTo($permission);
        }

        $this->command->info('✅ Permission pph21.index berhasil dibuat dan diberikan ke Super Admin.');
    }
}
