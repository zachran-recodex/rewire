<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create super admin in production, others should be created manually
        if (app()->environment('local', 'testing')) {
            // Create Super Admin user (only for local/testing)
            $superAdmin = User::create([
                'name' => 'Zachran Razendra',
                'username' => 'zachranraze',
                'email' => 'zachranraze@recodex.id',
                'password' => Hash::make('admin123'),
                'bio' => 'Super Administrator of Rewire',
                'location' => 'Indonesia',
                'is_active' => true,
            ]);
            $superAdmin->assignRole('super-admin');

            // Create Admin user (only for local/testing)
            $admin = User::create([
                'name' => 'Admin User',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'bio' => 'Administrator account',
                'is_active' => true,
            ]);
            $admin->assignRole('admin');

            // Create regular User (only for local/testing)
            $user = User::create([
                'name' => 'Regular User',
                'username' => 'user',
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
                'bio' => 'Regular user account',
                'is_active' => true,
            ]);
            $user->assignRole('user');
        } else {
            // Production: Create only super admin with secure defaults
            $superAdmin = User::create([
                'name' => env('ADMIN_NAME', 'Administrator'),
                'username' => env('ADMIN_USERNAME', 'admin'),
                'email' => env('ADMIN_EMAIL', 'admin@rewire.web.id'),
                'password' => Hash::make(env('ADMIN_PASSWORD', str()->random(32))),
                'bio' => 'System Administrator',
                'is_active' => true,
            ]);
            $superAdmin->assignRole('super-admin');
        }
    }
}
