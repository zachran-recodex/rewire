<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class UserForm extends Form
{
    public ?User $user = null;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|string|max:255')]
    public $username = '';

    #[Validate('required|email')]
    public $email = '';

    #[Validate('nullable|string|min:8')]
    public $password = '';

    #[Validate('required|exists:roles,id')]
    public $role_id = '';

    #[Validate('boolean')]
    public $is_active = true;

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->password = ''; // Always empty for security
        $this->role_id = $user->roles->first()?->id ?? '';
        $this->is_active = $user->is_active;
    }

    public function store(): User
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

        $this->reset();

        return $user;
    }

    public function update(): void
    {
        if (! $this->user) {
            throw new \Exception('No user set for update');
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$this->user->id,
            'email' => 'required|email|unique:users,email,'.$this->user->id,
            'password' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'is_active' => $this->is_active,
        ];

        if (! empty($this->password)) {
            $updateData['password'] = Hash::make($this->password);
        }

        $this->user->update($updateData);

        $role = Role::find($this->role_id);
        if ($role) {
            $this->user->syncRoles($role);
        }
    }
}
