<?php
namespace Database\Seeders;

use App\Models\Permission_group;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Koreksipermissionseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissiongroup = Permission_group::firstOrCreate(['name' => 'Koreksi']);

        Permission::firstOrCreate(['name' => 'koreksi.index'], ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'koreksi.create'], ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'koreksi.delete'], ['id_permission_group' => $permissiongroup->id]);
        Permission::firstOrCreate(['name' => 'koreksi.approve'], ['id_permission_group' => $permissiongroup->id]);

        $permissions = Permission::where('id_permission_group', $permissiongroup->id)->get();
        
        $roleID = 1; // super admin
        $role = Role::findById($roleID);
        foreach ($permissions as $p) {
             if ($role && !$role->hasPermissionTo($p)) $role->givePermissionTo($p);
        }

        $roleKaryawan = 3; // karyawan
        $rolekar = Role::findById($roleKaryawan);
        
        // Karyawan only gets create permission (to apply for correction)
        $karyawanPermissions = ['koreksi.create'];
        foreach ($karyawanPermissions as $pName) {
            $p = Permission::where('name', $pName)->first();
            if ($p && $rolekar && !$rolekar->hasPermissionTo($p)) {
                $rolekar->givePermissionTo($p);
            }
        }
    }
}
