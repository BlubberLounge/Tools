<?php

namespace App\Listeners;

use App\Events\BlubberLounge\SsoLoggedIn;

use App\Models\DartPlayerInvitation;

/**
 * After a "Sign in with BlubberLounge" login: keep the BlubberLounge ids and
 * tokens the dart API needs, and link pending player invitations.
 */
class BlubberLoungeLoginListener
{
    public function handle(SsoLoggedIn $event): void
    {
        $user = $event->user;
        $identity = $event->identity;

        // external_id is the Auth server's public user id (not the OIDC subject);
        // the dart API and acquaintances look users up by it.
        $externalId = $identity->profile['id'] ?? null;
        if (empty($user->external_id) && is_string($externalId) && $externalId !== '')
            $user->external_id = $externalId;

        $user->forceFill([
            'access_token' => $identity->accessToken,
            'refresh_token' => $identity->refreshToken,
            'token_expires_at' => now()->addSeconds($identity->expiresIn ?? 3600),
        ])->save();

        $invitation = DartPlayerInvitation::where('email', $user->email)
            ->whereIn('status', ['pending', 'sent'])
            ->first();

        if ($invitation)
            $invitation->markAsRegistered($user);
    }
}
