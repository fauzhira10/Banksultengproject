<?php

namespace App\Policies;

use App\Models\Tiket;
use App\Models\User;

class TiketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tiket $tiket): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canManageOperationalData();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tiket $tiket): bool
    {
        return $user->canManageOperationalData();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tiket $tiket): bool
    {
        return $user->canManageOperationalData();
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tiket $tiket): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can bulk restore models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tiket $tiket): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can bulk permanently delete models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
