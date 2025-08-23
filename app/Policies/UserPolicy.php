<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Super-admin can update anyone except other super-admins
        // Admin can update users but not super-admins or other admins
        // Users cannot update other users

        if ($user->hasRole('super-admin')) {
            return ! $model->hasRole('super-admin') || $user->id === $model->id;
        }

        if ($user->hasRole('admin')) {
            return $model->hasRole('user');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Super-admin can delete anyone except other super-admins
        // Admin can delete users but not super-admins or other admins
        // Users cannot delete other users
        // No one can delete themselves

        if ($user->id === $model->id) {
            return false; // Can't delete self
        }

        if ($user->hasRole('super-admin')) {
            return ! $model->hasRole('super-admin');
        }

        if ($user->hasRole('admin')) {
            return $model->hasRole('user');
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
