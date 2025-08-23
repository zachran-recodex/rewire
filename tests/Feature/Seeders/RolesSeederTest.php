<?php

declare(strict_types=1);

use Database\Seeders\RolesSeeder;
use Spatie\Permission\Models\Role;

test('roles seeder creates all required roles', function () {
    // Clear existing roles for clean test
    Role::query()->delete();
    
    // Run the seeder
    $this->artisan('db:seed', ['--class' => RolesSeeder::class])
        ->assertExitCode(0);
    
    // Verify all roles were created
    expect(Role::where('name', 'super-admin')->exists())->toBeTrue();
    expect(Role::where('name', 'admin')->exists())->toBeTrue();
    expect(Role::where('name', 'user')->exists())->toBeTrue();
    
    // Verify guard is set correctly
    expect(Role::where('name', 'super-admin')->first()->guard_name)->toBe('web');
    expect(Role::where('name', 'admin')->first()->guard_name)->toBe('web');
    expect(Role::where('name', 'user')->first()->guard_name)->toBe('web');
});

test('roles seeder is idempotent', function () {
    // Ensure roles exist first
    $this->artisan('db:seed', ['--class' => RolesSeeder::class]);
    
    $initialCount = Role::count();
    
    // Run seeder again
    $this->artisan('db:seed', ['--class' => RolesSeeder::class])
        ->assertExitCode(0);
    
    // Verify no duplicate roles were created
    expect(Role::count())->toBe($initialCount);
    
    // Verify roles still exist
    expect(Role::where('name', 'super-admin')->count())->toBe(1);
    expect(Role::where('name', 'admin')->count())->toBe(1);
    expect(Role::where('name', 'user')->count())->toBe(1);
});

test('roles seeder handles partial existing roles', function () {
    // Clear all roles
    Role::query()->delete();
    
    // Create only one role manually
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    
    // Run the seeder
    $this->artisan('db:seed', ['--class' => RolesSeeder::class])
        ->assertExitCode(0);
    
    // Verify all roles now exist
    expect(Role::count())->toBe(3);
    expect(Role::where('name', 'super-admin')->exists())->toBeTrue();
    expect(Role::where('name', 'admin')->exists())->toBeTrue();
    expect(Role::where('name', 'user')->exists())->toBeTrue();
});