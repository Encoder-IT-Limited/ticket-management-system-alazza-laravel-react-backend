<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure admin role exists
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Get all permission IDs
        $permissionIds = Permission::pluck('id')->all();

        // Attach all permissions to admin role
        $adminRole->permissions()->sync($permissionIds);

        if (isset($this->command)) {
            $this->command->info('Admin role synced with all permissions.');
        }
    }
}


