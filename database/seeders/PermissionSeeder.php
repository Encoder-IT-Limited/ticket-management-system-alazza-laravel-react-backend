<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Users Permissions
            ['name' => 'Create Users', 'category' => 'Users', 'slug' => 'create-users'],
            ['name' => 'Read Users', 'category' => 'Users', 'slug' => 'read-users'],
            ['name' => 'Update Users', 'category' => 'Users', 'slug' => 'update-users'],
            ['name' => 'Delete Users', 'category' => 'Users', 'slug' => 'delete-users'],
            ['name' => 'Export Users', 'category' => 'Users', 'slug' => 'export-users'],

            // Roles Permissions
            ['name' => 'Create Roles', 'category' => 'Roles', 'slug' => 'create-roles'],
            ['name' => 'Read Roles', 'category' => 'Roles', 'slug' => 'read-roles'],
            ['name' => 'Update Roles', 'category' => 'Roles', 'slug' => 'update-roles'],
            ['name' => 'Delete Roles', 'category' => 'Roles', 'slug' => 'delete-roles'],

            // Permissions Permissions
            ['name' => 'Create Permissions', 'category' => 'Permissions', 'slug' => 'create-permissions'],
            ['name' => 'Read Permissions', 'category' => 'Permissions', 'slug' => 'read-permissions'],
            ['name' => 'Update Permissions', 'category' => 'Permissions', 'slug' => 'update-permissions'],
            ['name' => 'Delete Permissions', 'category' => 'Permissions', 'slug' => 'delete-permissions'],

            // Tickets Permissions
            ['name' => 'Create Tickets', 'category' => 'Tickets', 'slug' => 'create-tickets'],
            ['name' => 'Read Tickets', 'category' => 'Tickets', 'slug' => 'read-tickets'],
            ['name' => 'Update Tickets', 'category' => 'Tickets', 'slug' => 'update-tickets'],
            ['name' => 'Delete Tickets', 'category' => 'Tickets', 'slug' => 'delete-tickets'],
            ['name' => 'Assign Tickets', 'category' => 'Tickets', 'slug' => 'assign-tickets'],
            ['name' => 'Close Tickets', 'category' => 'Tickets', 'slug' => 'close-tickets'],
            ['name' => 'Add Comments', 'category' => 'Tickets', 'slug' => 'add-comments'],

            // System Permissions
            ['name' => 'Dashboard', 'category' => 'System', 'slug' => 'dashboard'],
            ['name' => 'Update Logo', 'category' => 'System', 'slug' => 'update-logo'],
            ['name' => 'Export Database', 'category' => 'Reports', 'slug' => 'export-database'],

            // Reports Permissions
            ['name' => 'Read Reports', 'category' => 'Reports', 'slug' => 'read-reports'],
            ['name' => 'Export Reports', 'category' => 'Reports', 'slug' => 'export-reports'],

            // Activities Log Permissions
            ['name' => 'Activities Log', 'category' => 'System', 'slug' => 'activities-log'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        $this->command->info('permissions seeded successfully!');
    }
}
