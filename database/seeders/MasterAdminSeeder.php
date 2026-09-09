<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MasterAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dapatkan user adamadifa
        $user = User::where('username', 'adamadifa')->first();
        if (!$user) {
            $this->command->error("User 'adamadifa' tidak ditemukan.");
            return;
        }

        // 2. Buat atau dapatkan role master admin
        $role = Role::firstOrCreate(['name' => 'master admin']);

        // 3. Ambil semua permission yang ada di sistem
        $permissions = Permission::all();

        // 4. Assign semua permission ke role master admin
        $role->syncPermissions($permissions);

        // 5. Assign role master admin ke user adamadifa
        if (!$user->hasRole('master admin')) {
            $user->assignRole($role);
        }

        $this->command->info("Role 'master admin' berhasil dibuat dengan " . count($permissions) . " permission dan di-assign ke user 'adamadifa'.");
    }
}
