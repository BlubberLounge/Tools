<?php

namespace App\Policies;

use App\Models\Hookah;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HookahPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return void|bool
     */
    public function before(User $user, $ability)
    {
        if(!$user->role)
            return false;

        if($user->role->id > Role::ADMIN)
            return false;
        
        return true;
    }
    
    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Hookah $hookah)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Hookah $hookah)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Hookah $hookah)
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Hookah $hookah)
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Hookah $hookah)
    {
        return true;
    }
}
