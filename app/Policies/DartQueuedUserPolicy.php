<?php

namespace App\Policies;

use App\Models\DartQueuedUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DartQueuedUserPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, $ability): bool|null
    {
        return $user->level() >= 5 ?: null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewany.dart.queued.user');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DartQueuedUser $dartQueuedUser): bool
    {
        return $user->hasPermission('view.dart.queued.user');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create.dart.queued.user');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DartQueuedUser $dartQueuedUser): bool
    {
        return $user->hasPermission('update.dart.queued.user');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DartQueuedUser $dartQueuedUser): bool
    {
        return $user->hasPermission('delete.dart.queued.user');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DartQueuedUser $dartQueuedUser): bool
    {
        return $user->hasPermission('restore.dart.queued.user');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DartQueuedUser $dartQueuedUser): bool
    {
        return $user->hasPermission('forcedelete.dart.queued.user');
    }
}
