<?php

namespace App\Policies;

use App\Models\Terminal;
use App\Models\User;

class TerminalPolicy
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
    public function view(User $user, Terminal $terminal): bool
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
    public function update(User $user, Terminal $terminal): bool
    {
        return $user->canManageOperationalData();
    }

    /**
     * Determine whether the user can import terminals from a spreadsheet.
     */
    public function import(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Terminal $terminal): bool
    {
        return $user->isAdmin();
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
    public function restore(User $user, Terminal $terminal): bool
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
     *
     * Terminal yang masih memiliki riwayat tiket tidak boleh dihapus permanen karena
     * tiketnya menjadi bukti perhitungan SLA.
     */
    public function forceDelete(User $user, Terminal $terminal): bool
    {
        return $user->isAdmin() && ! $terminal->tikets()->withTrashed()->exists();
    }

    /**
     * Determine whether the user can bulk permanently delete models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
