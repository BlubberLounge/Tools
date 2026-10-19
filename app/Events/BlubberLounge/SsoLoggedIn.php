<?php

namespace App\Events\BlubberLounge;

use App\Models\User;
use App\Services\BlubberLoungeAuth\OidcIdentity;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A user signed in with BlubberLounge (after Auth::login and the session
 * regeneration). The identity carries the access and refresh token.
 */
class SsoLoggedIn
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly OidcIdentity $identity,
    ) {}
}
