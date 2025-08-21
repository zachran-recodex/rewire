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
        // Create Super Admin user
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

        // Create Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'bio' => 'Administrator account',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create regular User
        $user = User::create([
            'name' => 'Regular User',
            'username' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'bio' => 'Regular user account',
            'is_active' => true,
        ]);
        $user->assignRole('user');
    }
}
