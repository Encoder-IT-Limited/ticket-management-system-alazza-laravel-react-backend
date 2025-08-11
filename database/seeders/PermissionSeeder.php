<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            // System Settings Permissions
            ['name' => 'Dashboard', 'category' => 'System Settings'],
            ['name' => 'Change Password', 'category' => 'System Settings'],
            ['name' => 'Activities Log', 'category' => 'System Settings'],

            // User Management Permissions
            ['name' => 'View Users', 'category' => 'User Management'],
            ['name' => 'Create Users', 'category' => 'User Management'],
            ['name' => 'Edit Users', 'category' => 'User Management'],
            ['name' => 'Delete Users', 'category' => 'User Management'],
            ['name' => 'Assign Roles', 'category' => 'User Management'],
            
            // Role Management Permissions
            ['name' => 'View Roles', 'category' => 'Role Management'],
            ['name' => 'Create Roles', 'category' => 'Role Management'],
            ['name' => 'Edit Roles', 'category' => 'Role Management'],
            ['name' => 'Delete Roles', 'category' => 'Role Management'],
            ['name' => 'Assign Permissions', 'category' => 'Role Management'],
            
            // Ticket Management Permissions
            ['name' => 'View Tickets', 'category' => 'Ticket Management'],
            ['name' => 'Create Tickets', 'category' => 'Ticket Management'],
            ['name' => 'Edit Tickets', 'category' => 'Ticket Management'],
            ['name' => 'Delete Tickets', 'category' => 'Ticket Management'],
            ['name' => 'Assign Tickets', 'category' => 'Ticket Management'],
            ['name' => 'Close Tickets', 'category' => 'Ticket Management'],
            ['name' => 'Reopen Tickets', 'category' => 'Ticket Management'],
            ['name' => 'Add Comments', 'category' => 'Ticket Management'],
            ['name' => 'View Ticket History', 'category' => 'Ticket Management'],
            
            // Report Permissions
            ['name' => 'View Reports', 'category' => 'Reports'],
            ['name' => 'Generate Reports', 'category' => 'Reports'],
            ['name' => 'Export Reports', 'category' => 'Reports'],
            ['name' => 'View Analytics', 'category' => 'Reports'],
            ['name' => 'View Dashboard', 'category' => 'Reports'],
            
          
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        $this->command->info('permissions seeded successfully!');
    }
}
