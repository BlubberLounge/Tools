<?php

namespace App\Policies;

use App\Models\Cocktail;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CocktailPolicy
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
        return $user->hasPermission('viewany.cocktail');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cocktail $cocktail): bool
    {
        return $user->hasPermission('view.cocktail');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create.cocktail');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cocktail $cocktail): bool
    {
        return $user->hasPermission('update.cocktail');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cocktail $cocktail): bool
    {
        return $user->hasPermission('delete.cocktail');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cocktail $cocktail): bool
    {
        return $user->hasPermission('restore.cocktail');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cocktail $cocktail): bool
    {
        return $user->hasPermission('forcedelete.cocktail');
    }
}
