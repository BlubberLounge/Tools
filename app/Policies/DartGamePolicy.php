<?php

namespace App\Policies;

use App\Models\DartGame;
use App\Models\User;

class DartGamePolicy
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
        return $user->hasPermission('viewany.dart.game');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DartGame $dartGame): bool
    {
        return $user->hasPermission('view.dart.game')
            && (
                $dartGame->createdBy()->is($user)
                || $dartGame->users()->where('user_id', $user->id)->exists()
            );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create.dart.game');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DartGame $dartGame): bool
    {
        return $user->hasPermission('update.dart.game') || $dartGame->createdBy()->is($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DartGame $dartGame): bool
    {
        return $user->hasPermission('delete.dart.game') || $dartGame->createdBy()->is($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DartGame $dartGame): bool
    {
        return $user->hasPermission('restore.dart.game') || $dartGame->createdBy()->is($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DartGame $dartGame): bool
    {
        return $user->hasPermission('forcedelete.dart.game');
    }
}
