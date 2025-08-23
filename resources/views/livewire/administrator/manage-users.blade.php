<div class="space-y-6">
    @if (session()->has('message'))
        <flux:callout color="green" icon="check-circle" inline="" wire:key="callout-{{ session('message_timestamp', time()) }}" x-data="{ visible: true }" x-show="visible">
            <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">{{ session('message') }}</flux:callout.heading>
            <x-slot name="controls">
                <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
            </x-slot>
        </flux:callout>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="2">Manage Users</flux:heading>
            <flux:breadcrumbs class="mt-2">
                <flux:breadcrumbs.item href="{{ route('dashboard') }}" separator="slash" icon="home">Home</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="#" separator="slash">Administrator</flux:breadcrumbs.item>
                <flux:breadcrumbs.item separator="slash">Manage Users</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="$set('showCreateModal', true)">
            Create
        </flux:button>
    </div>

    <!-- Search -->
    <div class="flex gap-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search users..." icon="magnifying-glass" />
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Account
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Username
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Role
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <flux:avatar size="lg" :name="$user->name" />
                                    </div>
                                    <div class="ml-4">
                                        <flux:heading size="lg">
                                            {{ $user->name }}
                                        </flux:heading>
                                        <flux:text>
                                            {{ $user->email }}
                                        </flux:text>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:heading>
                                    {{ $user->username }}
                                </flux:heading>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($user->roles->isNotEmpty())
                                    @php
                                        $roleName = $user->roles->first()->name;
                                        $roleColor = match($roleName) {
                                            'super-admin' => 'red',
                                            'admin' => 'blue',
                                            'user' => 'green',
                                            default => 'zinc'
                                        };
                                    @endphp
                                    <flux:badge variant="pill" :color="$roleColor">
                                        {{ ucwords(str_replace('-', ' ', $roleName)) }}
                                    </flux:badge>
                                @else
                                    <flux:badge variant="pill">No Role</flux:badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($user->is_active)
                                    <flux:badge icon="check-circle" color="green">Active</flux:badge>
                                @else
                                    <flux:badge icon="x-circle" color="red">Inactive</flux:badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex justify-end">
                                    <flux:button.group>
                                        <flux:modal.trigger name="show-{{ $user->id }}">
                                            <flux:button icon="eye" />
                                        </flux:modal.trigger>
                                        @can('update', $user)
                                            <flux:button icon="pencil-square" wire:click="edit({{ $user->id }})" />
                                        @endcan
                                        @can('delete', $user)
                                            <flux:modal.trigger name="delete-{{ $user->id }}">
                                                <flux:button icon="trash" variant="danger" />
                                            </flux:modal.trigger>
                                        @endcan
                                    </flux:button.group>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-zinc-400 dark:text-zinc-500">
                                    <flux:icon.users class="mx-auto h-12 w-12 mb-4" />
                                    <p class="text-lg font-medium">No users found</p>
                                    <p class="text-sm">Get started by creating a new user.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if ($this->users->hasPages())
        <div class="mt-6">
            {{ $this->users->links() }}
        </div>
    @endif

    <!-- Show Modals -->
    @foreach ($this->users as $user)
        <flux:modal name="show-{{ $user->id }}" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">User Details</flux:heading>
                    <flux:text class="mt-2">View user information.</flux:text>
                </div>

                <div class="space-y-4">
                    <div class="text-center">
                        <flux:avatar size="xl" :name="$user->name" class="mx-auto mb-4" />
                        <flux:heading size="lg">{{ $user->name }}</flux:heading>
                        <flux:text>{{ $user->username }}</flux:text>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500">Email</flux:text>
                            <flux:text>{{ $user->email }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500">Role</flux:text>
                            <flux:text>{{ $user->roles->first()?->name ? ucwords(str_replace('-', ' ', $user->roles->first()->name)) : 'No Role' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500">Status</flux:text>
                            <flux:text>{{ $user->is_active ? 'Active' : 'Inactive' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500">Created</flux:text>
                            <flux:text>{{ $user->created_at->format('M j, Y') }}</flux:text>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">Close</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>
    @endforeach

    <!-- Create Modal -->
    <flux:modal wire:model.self="showCreateModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Create New User</flux:heading>
                <flux:text class="mt-2">Add a new user to the system.</flux:text>
            </div>

            <form wire:submit="create">
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Full Name</flux:label>
                        <flux:input wire:model="form.name" placeholder="Enter full name" required />
                        <flux:error name="form.name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Username</flux:label>
                        <flux:input wire:model="form.username" placeholder="Enter username" required />
                        <flux:error name="form.username" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input wire:model="form.email" type="email" placeholder="Enter email address" required />
                        <flux:error name="form.email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Password</flux:label>
                        <flux:input wire:model="form.password" type="password" placeholder="Enter password" required />
                        <flux:error name="form.password" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:select wire:model="form.role_id" placeholder="Select a role" required>
                            @foreach ($this->roles as $role)
                                <flux:select.option value="{{ $role->id }}">{{ ucwords(str_replace('-', ' ', $role->name)) }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="form.role_id" />
                    </flux:field>

                    <flux:field>
                        <flux:checkbox wire:model="form.is_active" label="Active" description="User can log in and access the system" />
                        <flux:error name="form.is_active" />
                    </flux:field>
                </div>

                <div class="flex gap-2 mt-6">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary">
                        Create
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Edit Modal -->
    <flux:modal wire:model.self="showEditModal" class="md:w-96">
        @if($editing)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Edit User</flux:heading>
                    <flux:text class="mt-2">Update user information for {{ $editing->name }}.</flux:text>
                </div>

                <form wire:submit="update">
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>Full Name</flux:label>
                            <flux:input wire:model="form.name" placeholder="Enter full name" required />
                            <flux:error name="form.name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Username</flux:label>
                            <flux:input wire:model="form.username" placeholder="Enter username" required />
                            <flux:error name="form.username" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Email</flux:label>
                            <flux:input wire:model="form.email" type="email" placeholder="Enter email address" required />
                            <flux:error name="form.email" />
                        </flux:field>

                        <flux:field>
                            <flux:label>New Password</flux:label>
                            <flux:input wire:model="form.password" type="password" placeholder="Leave blank to keep current password" />
                            <flux:error name="form.password" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Role</flux:label>
                            <flux:select wire:model="form.role_id" placeholder="Select a role" required>
                                @foreach ($this->roles as $role)
                                    <flux:select.option value="{{ $role->id }}">{{ ucwords(str_replace('-', ' ', $role->name)) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="form.role_id" />
                        </flux:field>

                        <flux:field>
                            <flux:checkbox wire:model="form.is_active" label="Active" description="User can log in and access the system" />
                            <flux:error name="form.is_active" />
                        </flux:field>
                    </div>

                    <div class="flex gap-2 mt-6">
                        <flux:spacer />

                        <flux:modal.close>
                            <flux:button variant="ghost">Cancel</flux:button>
                        </flux:modal.close>

                        <flux:button type="submit" variant="primary">
                            Update
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif
    </flux:modal>

    <!-- Delete Modals -->
    @foreach ($this->users as $user)
        @can('delete', $user)
            <flux:modal :name="'delete-'.$user->id" class="min-w-[22rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Delete User?</flux:heading>
                        <flux:text class="mt-2">
                            <p>You're about to delete <strong>{{ $user->name }}</strong>.</p>
                            <p>This action cannot be reversed.</p>
                        </flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost">Cancel</flux:button>
                        </flux:modal.close>
                        <flux:button wire:click="delete({{ $user->id }})" variant="danger">
                            Delete
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        @endcan
    @endforeach

</div>