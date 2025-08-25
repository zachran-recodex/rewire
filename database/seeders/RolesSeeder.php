<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles (idempotent - only create if they don't exist)
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'web']
        );

        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web']
        );

        $user = Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web']
        );

        // Log the results for debugging
        $this->command->info('Roles seeded successfully:');
        $this->command->info('- super-admin: '.($superAdmin->wasRecentlyCreated ? 'created' : 'already exists'));
        $this->command->info('- admin: '.($admin->wasRecentlyCreated ? 'created' : 'already exists'));
        $this->command->info('- user: '.($user->wasRecentlyCreated ? 'created' : 'already exists'));

        // Reset cache again after creating roles
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
