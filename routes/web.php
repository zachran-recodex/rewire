<?php

use App\Livewire\Administrator\ManageUsers;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('index');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->prefix('dashboard')->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Administrator routes
    Route::middleware(['role:super-admin|admin'])->prefix('administrator')->name('administrator.')->group(function () {
        Route::get('manage-users', ManageUsers::class)->name('manage-users');
    });
});

require __DIR__.'/auth.php';
