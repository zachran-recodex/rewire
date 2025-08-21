<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $avatar;
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $phone = '';
    public string $bio = '';
    public string $location = '';
    public string $website = '';
    public string $birth_date = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->username = $user->username ?? '';
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->bio = $user->bio ?? '';
        $this->location = $user->location ?? '';
        $this->website = $user->website ?? '';
        $this->birth_date = $user->birth_date ? $user->birth_date->format('Y-m-d') : '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'avatar' => ['nullable', 'image', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before:today'],
        ]);

        // Handle avatar upload
        if ($this->avatar) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $avatarPath = $this->avatar->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        } else {
            // Remove avatar from validated data if not uploaded
            unset($validated['avatar']);
        }

        // Remove username from validated data to prevent updates
        unset($validated['username']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your profile information')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <!-- Avatar Upload -->
            <div class="flex flex-col items-center space-y-4">
                <div class="flex flex-col items-center space-y-4">
                    <!-- Current Avatar -->
                    <div>
                        @if (auth()->user()->avatar)
                            <flux:avatar
                                src="{{ Storage::url(auth()->user()->avatar) }}"
                                :name="auth()->user()->name"
                                size="xl"
                            />
                        @else
                            <flux:avatar
                                :name="auth()->user()->name"
                                size="xl"
                            />
                        @endif
                    </div>

                    <!-- Preview New Avatar -->
                    @if ($avatar)
                        <div class="flex flex-col items-center space-y-2">
                            <flux:text class="text-sm font-medium text-gray-700 dark:text-gray-300">Preview:</flux:text>
                            <img
                                src="{{ $avatar->temporaryUrl() }}"
                                alt="Avatar Preview"
                                class="size-16 rounded-xl object-cover"
                            />
                            <div class="px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900 border border-blue-300 dark:border-blue-700 flex items-center space-x-2">
                                <flux:icon name="camera" variant="mini" class="text-blue-600 dark:text-blue-300" />
                                <span class="text-sm text-blue-600 dark:text-blue-300 font-medium">New image selected</span>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col items-center space-y-2">
                    <flux:input
                        wire:model="avatar"
                        :label="__('Profile Picture')"
                        type="file"
                        accept="image/*"
                        class="text-sm"
                    />
                    <flux:text class="text-xs">
                        Maximum file size: 2MB. Supported formats: JPG, PNG, GIF
                    </flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input wire:model="name" :label="__('Full Name')" type="text" required autofocus autocomplete="name" />
                <flux:input wire:model="username" :label="__('Username')" type="text" readonly disabled class="bg-gray-100 dark:bg-gray-800 text-gray-500 cursor-not-allowed" />
            </div>
            <flux:text class="text-sm">
                Username cannot be changed for security reasons.
            </flux:text>

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input wire:model="phone" :label="__('Phone Number')" type="tel" autocomplete="tel" />
                <flux:input wire:model="birth_date" :label="__('Birth Date')" type="date" />
            </div>

            <flux:input wire:model="location" :label="__('Location')" type="text" autocomplete="address-level2" />

            <flux:input wire:model="website" :label="__('Website')" type="url" placeholder="https://example.com" />

            <div>
                <flux:textarea wire:model="bio" :label="__('Bio')" rows="4" placeholder="Tell us about yourself..." />
                <flux:text class="mt-1 text-sm text-gray-500">
                    Maximum 1000 characters
                </flux:text>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
