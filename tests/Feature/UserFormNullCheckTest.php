<?php

declare(strict_types=1);

use App\Livewire\Administrator\ManageUsers;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Seed roles
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesSeeder']);
});

test('manage users component handles null editing state gracefully', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(ManageUsers::class)
        ->call('update')
        ->assertHasNoErrors();
});

test('manage users component properly resets form on modal close', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $user = User::factory()->create();
    $user->assignRole('user');

    Livewire::actingAs($admin)
        ->test(ManageUsers::class)
        ->call('edit', $user->id)
        ->assertSet('editing.id', $user->id)
        ->assertSet('showEditModal', true)
        ->call('closeEditModal')
        ->assertSet('editing', null)
        ->assertSet('showEditModal', false);
});

test('user form validation prevents update without user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $userRole = Role::where('name', 'user')->first();

    $component = Livewire::actingAs($admin)
        ->test(ManageUsers::class)
        ->set('form.name', 'Test User')
        ->set('form.username', 'testuser')
        ->set('form.email', 'test@example.com')
        ->set('form.role_id', $userRole->id)
        ->set('form.is_active', true);

    // Try to update without setting editing user
    $component->call('update')
        ->assertHasNoErrors(); // Should return early without errors
});

test('editing user status can be toggled safely', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('user');

    Livewire::actingAs($admin)
        ->test(ManageUsers::class)
        ->call('edit', $user->id)
        ->assertSet('editing.id', $user->id)
        ->set('form.is_active', false) // Toggle status from true to false
        ->call('update')
        ->assertHasNoErrors()
        ->assertSet('editing', null) // Should be reset after update
        ->assertSet('showEditModal', false); // Modal should be closed

    $user->refresh();
    expect($user->is_active)->toBeFalse(); // Status should be updated
});
