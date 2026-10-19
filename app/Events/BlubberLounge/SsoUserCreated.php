<?php

namespace App\Events\BlubberLounge;

use App\Models\User;
use App\Services\BlubberLoungeAuth\OidcIdentity;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A local user was created for a first single sign-on login
 * (fired inside the transaction, before SsoLoggedIn).
 */
class SsoUserCreated
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly OidcIdentity $identity,
    ) {}
}
