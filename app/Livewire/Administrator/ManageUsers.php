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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $user = $this->form->store();

        $this->modal('create')->close();
        session()->flash('message', 'User created successfully.');
        session()->flash('message_timestamp', microtime(true));
    }

    public function edit(User $user): void
    {
        $this->authorize('update', $user);

        $this->editing = $user;
        $this->form->setUser($user);
    }

    public function update(): void
    {
        $this->authorize('update', $this->editing);

        $this->form->update();

        $this->dispatch('close-modal', 'edit-'.$this->editing->id);
        $this->editing = null;
        session()->flash('message', 'User updated successfully.');
        session()->flash('message_timestamp', microtime(true));
    }

    public function delete(User $user): void
    {
        $this->authorize('delete', $user);

        $user->delete();
        $this->modal('delete-'.$user->id)->close();
        session()->flash('message', 'User deleted successfully.');
        session()->flash('message_timestamp', microtime(true));
    }

    public function closeModal(string $modalName = ''): void
    {
        if ($modalName) {
            $this->modal($modalName)->close();
        }

        // Reset editing state when closing modals
        if ($this->editing) {
            $this->editing = null;
            $this->form->reset();
        }

        $this->dispatch('modal-close', name: $modalName);
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
