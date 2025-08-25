<?php

declare(strict_types=1);

use App\Livewire\Administrator\ManageUsers;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    // Seed roles
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesSeeder']);
});

test('modals render correctly with unique wire keys', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $users = User::factory()->count(3)->create();
    $users->each(fn ($user) => $user->assignRole('user'));

    Livewire::actingAs($admin)
        ->test(ManageUsers::class)
        ->assertSee('Manage Users')
        ->assertSee('show-modal-'.$users->first()->id)
        ->assertSee('delete-modal-'.$users->first()->id);
});

test('show modal does not interfere with delete modal', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('user');

    Livewire::actingAs($admin)
        ->test(ManageUsers::class)
        ->assertSee("show-{$user->id}")
        ->assertSee("delete-{$user->id}")
        ->assertSee('show-modal-'.$user->id)
        ->assertSee('delete-modal-'.$user->id);
});

test('edit modal state does not interfere with other modals', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('user');

    Livewire::actingAs($admin)
        ->test(ManageUsers::class)
        ->call('edit', $user->id)
        ->assertSet('showEditModal', true)
        ->assertSet('editing.id', $user->id)
        ->call('closeEditModal')
        ->assertSet('showEditModal', false)
        ->assertSet('editing', null)
        ->assertSee("delete-{$user->id}");
});

test('all modals have proper flux button configurations', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('user');

    Livewire::actingAs($admin)
        ->test(ManageUsers::class)
        ->assertSee('show-'.$user->id)
        ->assertSee('delete-'.$user->id)
        ->assertSee('edit('.$user->id.')');
});
