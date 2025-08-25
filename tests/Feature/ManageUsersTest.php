<?php

use App\Livewire\Administrator\ManageUsers;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'super-admin']);
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'user']);
});

it('can render manage users component', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/dashboard/administrator/manage-users')
        ->assertSuccessful()
        ->assertSeeLivewire(ManageUsers::class);
});

it('can create user with form object', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $userRole = Role::where('name', 'user')->first();

    Livewire::actingAs($admin)
        ->test(ManageUsers::class)
        ->set('form.name', 'Test User')
        ->set('form.username', 'testuser')
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'password123')
        ->set('form.role_id', $userRole->id)
        ->set('form.is_active', true)
        ->call('create')
        ->assertHasNoErrors();

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

it('can edit user with authorization', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('user');

    Livewire::actingAs($admin)
        ->test(ManageUsers::class)
        ->call('edit', $user->id)
        ->assertSet('editing.id', $user->id)
        ->assertHasNoErrors();
});

it('prevents unauthorized user deletion', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $targetUser = User::factory()->create();
    $targetUser->assignRole('admin');

    Livewire::actingAs($user)
        ->test(ManageUsers::class)
        ->call('delete', $targetUser->id)
        ->assertForbidden();
});

it('shows only authorized action buttons', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($user)
        ->get('/dashboard/administrator/manage-users')
        ->assertDontSee('wire:click="edit('.$admin->id.')"', false)
        ->assertDontSee('wire:click="delete('.$admin->id.')"', false);
});
