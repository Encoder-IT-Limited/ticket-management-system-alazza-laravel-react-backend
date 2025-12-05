<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // First, create the new role_id column
            $table->foreignId('role_id')->nullable()->after('role');
            
            // Add foreign key constraint
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });

        // Update existing users to have role_id based on their current role
        // This assumes you have a 'client' role in the roles table
        DB::statement("
            UPDATE users 
            SET role_id = (SELECT id FROM roles WHERE name = 'client' LIMIT 1)
            WHERE role = 'client'
        ");

        // Update admin users if they exist
        DB::statement("
            UPDATE users 
            SET role_id = (SELECT id FROM roles WHERE name = 'admin' LIMIT 1)
            WHERE role = 'admin'
        ");

        Schema::table('users', function (Blueprint $table) {
            // Drop the old role column
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back the old role column
            $table->string('role')->default('client')->after('status');
        });

        // Restore role values based on role_id
        DB::statement("
            UPDATE users u
            JOIN roles r ON u.role_id = r.id
            SET u.role = r.name
        ");

        Schema::table('users', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['role_id']);
            
            // Drop the role_id column
            $table->dropColumn('role_id');
        });
    }
};
