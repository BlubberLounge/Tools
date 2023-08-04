<?php

namespace App\Policies;

use App\Models\DartGame;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DartGamePolicy
{

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, $ability): bool
    {
        return true;
    }

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
    public function view(User $user, DartGame $dartGame): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DartGame $dartGame): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DartGame $dartGame): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DartGame $dartGame): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DartGame $dartGame): bool
    {
        return true;
    }
}
