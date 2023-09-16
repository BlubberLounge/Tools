<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;


class InvitationPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, $ability): bool|null
    {
        return $user?->level() >= 5 ?: null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewAny.invitation');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invitation $invitation): bool
    {
        return $user->hasPermission('view.invitation');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {

        return $user->hasPermission('create.invitation');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invitation $invitation): bool
    {
        return $user->hasPermission('update.invitation');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invitation $invitation): bool
    {
        return $user->hasPermission('delete.invitation');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invitation $invitation): bool
    {
        return $user->hasPermission('restore.invitation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invitation $invitation): bool
    {
        return $user->hasPermission('forcedelete.invitation');
    }

    /**
     *
     */
    public function approve(User $user, Invitation $invitation): bool
    {
        return $user->hasPermission('update.invitation');
    }

    /**
     *
     */
    public function denie(User $user, Invitation $invitation): bool
    {
        return $user->hasPermission('update.invitation');
    }
}
