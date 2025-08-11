<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // User::firstOrCreate([
        //     'email' => 'dev@fazit.sa',
        // ], [
        //     'name' => 'Admin',
        //     'email_verified_at' => now(),
        //     'password' => Hash::make('12345678'),
        //     'role' => 'admin',
        //     'is_super_admin' => true,
        // ]);

        // User::firstOrCreate([
        //     'name' => 'Admin',
        //     'email' => 'admin@admin.com',
        //     'email_verified_at' => now(),
        //     'password' => Hash::make('admin@admin.com'),
        //     'role' => 'admin',
        //     'is_super_admin' => true,
        // ]);

        $this->call([
            // CategorySeeder::class,
            PermissionSeeder::class
        ]);
    }
}
