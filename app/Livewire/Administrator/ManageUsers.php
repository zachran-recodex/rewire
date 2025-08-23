<?php

namespace App\Livewire\Administrator;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class ManageUsers extends Component
{
    use WithPagination;

    public $search = '';

    public UserForm $form;

    public ?User $editing = null;

    public bool $showEditModal = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public bool $showCreateModal = false;

    public function updatedShowCreateModal($value): void
    {
        if ($value) {
            // Reset form when opening create modal
            $this->form->reset();
        }
    }

    public function updatedShowEditModal($value): void
    {
        if (! $value) {
            // Reset form and editing state when closing edit modal
            $this->form->reset();
            $this->editing = null;
        }
    }

    public function create(): void
    {
        $user = $this->form->store();

        $this->showCreateModal = false;
        session()->flash('message', 'User created successfully.');
        session()->flash('message_timestamp', microtime(true));
    }

    public function edit(User $user): void
    {
        $this->authorize('update', $user);

        $this->editing = $user;
        $this->form->setUser($user);
        $this->showEditModal = true;
    }

    public function update(): void
    {
        if (! $this->editing) {
            return;
        }

        $this->authorize('update', $this->editing);

        $this->form->update();

        $this->showEditModal = false;
        session()->flash('message', 'User updated successfully.');
        session()->flash('message_timestamp', microtime(true));

        // Reset after successful update
        $this->form->reset();
        $this->editing = null;
    }

    public function delete(User $user): void
    {
        $this->authorize('delete', $user);

        $user->delete();
        session()->flash('message', 'User deleted successfully.');
        session()->flash('message_timestamp', microtime(true));
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->form->reset();
        $this->editing = null;
    }


    #[Computed]
    public function users()
    {
        return User::query()
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
    }

    #[Computed]
    public function roles()
    {
        return Role::where('name', '!=', 'super-admin')->get();
    }

    public function render()
    {
        return view('livewire.administrator.manage-users');
    }
}
