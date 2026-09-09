<?php

namespace Database\Seeders;

use App\Models\Permission_group;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProjectPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create/find the Permission Group
        $permissiongroup = Permission_group::firstOrCreate(
            ['name' => 'Project Management']
        );

        // 2. Define all permissions
        $permissions = [
            'project.index',
            'project.create',
            'project.edit',
            'project.delete',
            'project.show',
            'project.task.create',
            'project.task.edit',
            'project.task.delete',
            'project.task.assign',
            'project.report',
            'projectcategory.index',
            'projectcategory.create',
            'projectcategory.edit',
            'projectcategory.delete',
        ];

        // 3. Create permissions under the group
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName],
                ['id_permission_group' => $permissiongroup->id]
            );
        }

        // 4. Assign all permissions to Administrator (Role ID 1)
        $adminRole = Role::findById(1);
        if ($adminRole) {
            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$adminRole->hasPermissionTo($permission)) {
                    $adminRole->givePermissionTo($permission);
                    $this->command->info("Permission '{$permissionName}' assigned to Admin role.");
                }
            }
        } else {
            $this->command->error("Administrator role (ID 1) not found.");
        }

        // 5. Assign view-only permissions to Karyawan role
        $karyawanRole = Role::where('name', 'karyawan')->first();
        if ($karyawanRole) {
            $karyawanPermissions = [
                'project.index',
                'project.show',
            ];
            foreach ($karyawanPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$karyawanRole->hasPermissionTo($permission)) {
                    $karyawanRole->givePermissionTo($permission);
                    $this->command->info("Permission '{$permissionName}' assigned to Karyawan role.");
                }
            }
        } else {
            $this->command->warn("Karyawan role not found. Skipping assignment for Karyawan.");
        }
    }
}
