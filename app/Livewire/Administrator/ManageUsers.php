<?php

namespace App\Livewire\Administrator;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class ManageUsers extends Component
{
    use WithPagination;

    public $search = '';

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|string|max:255|unique:users,username')]
    public $username = '';

    #[Validate('required|email|unique:users,email')]
    public $email = '';

    #[Validate('required|string|min:8')]
    public $password = '';

    #[Validate('required|exists:roles,id')]
    public $role_id = '';

    #[Validate('boolean')]
    public $is_active = true;

    public $editForm = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'is_active' => $this->is_active,
        ]);

        $role = Role::find($this->role_id);
        $user->assignRole($role);

        $this->resetForm();
        $this->modal('create')->close();
        session()->flash('message', 'User created successfully.');
        session()->flash('message_timestamp', microtime(true));
    }

    public function updateUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $formData = $this->editForm[$userId];

        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$userId,
            'email' => 'required|email|unique:users,email,'.$userId,
            'password' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ];

        $this->validate(array_combine(
            array_map(fn ($key) => "editForm.{$userId}.{$key}", array_keys($rules)),
            $rules
        ));

        $updateData = [
            'name' => $formData['name'],
            'username' => $formData['username'],
            'email' => $formData['email'],
            'is_active' => $formData['is_active'] ?? false,
        ];

        if (! empty($formData['password'])) {
            $updateData['password'] = Hash::make($formData['password']);
        }

        $user->update($updateData);

        $role = Role::find($formData['role_id']);
        $user->syncRoles($role);

        $this->modal('edit-'.$userId)->close();
        session()->flash('message', 'User updated successfully.');
        session()->flash('message_timestamp', microtime(true));
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
        $this->modal('delete-'.$user->id)->close();
        session()->flash('message', 'User deleted successfully.');
        session()->flash('message_timestamp', microtime(true));
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->username = '';
        $this->email = '';
        $this->password = '';
        $this->role_id = '';
        $this->is_active = true;
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super-admin');
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('username', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(10);

        $roles = Role::where('name', '!=', 'super-admin')->get();

        foreach ($users as $user) {
            if (! isset($this->editForm[$user->id])) {
                $this->editForm[$user->id] = [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'password' => '',
                    'role_id' => $user->roles->first()?->id ?? '',
                    'is_active' => $user->is_active,
                ];
            }
        }

        return view('livewire.administrator.manage-users', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
