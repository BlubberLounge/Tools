<?php

namespace App\Services\BlubberLoungeAuth;

use App\Events\BlubberLounge\SsoUserCreated;
use App\Models\User;
use App\Services\BlubberLoungeAuth\Exceptions\OAuthLoginException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Finds or creates the local user for a single sign-on identity.
 *
 * 1. A user with the same auth_sub is the same person. The stored email is
 *    only updated when Auth reports the new address as verified and no other
 *    local user has it. The name is not synced (user names are unique here).
 * 2. Otherwise an existing user with the same email and no auth_sub yet is
 *    linked once, and only when Auth reports the email as verified and the
 *    local account verified it too. So neither registering the address at
 *    Auth nor registering it locally lets someone take over the account.
 *    Linking resets "remember me" and ends the account's existing sessions.
 * 3. Otherwise a new user is created, again only for a verified email.
 */
class OidcUserResolver
{
    public function resolve(OidcIdentity $identity): User
    {
        return DB::transaction(function () use ($identity) {
            $user = User::where('auth_sub', $identity->sub)->first();

            if ($user) {
                $this->syncProfile($user, $identity);

                return $user;
            }

            if (! $identity->emailVerified) {
                throw new OAuthLoginException(
                    'Your BlubberLounge email address is not verified. Verify it at BlubberLounge or contact an administrator.',
                    'email_not_verified'
                );
            }

            $user = User::where('email', $identity->email)->first();

            if ($user) {
                if ($user->auth_sub !== null && $user->auth_sub !== '') {
                    // The email belongs to a different BlubberLounge account.
                    throw new OAuthLoginException(
                        'This email address is already linked to another BlubberLounge account. Please contact an administrator.',
                        'email_linked_to_other_subject'
                    );
                }

                if ($user->email_verified_at === null) {
                    // Someone may have registered the address locally without owning it
                    // (account pre-hijacking); only a locally verified address is linked.
                    throw new OAuthLoginException(
                        'An account with this email address exists here but is not verified. Please contact an administrator.',
                        'local_email_not_verified'
                    );
                }

                $user->forceFill([
                    'auth_sub' => $identity->sub,
                    'remember_token' => Str::random(60),
                ])->save();
                $this->endOtherSessions($user);

                return $user;
            }

            $user = new User;
            $user->forceFill([
                'name' => User::uniqueName($identity->name),
                'firstname' => $identity->profile['firstname'] ?? $identity->name,
                'lastname' => $identity->profile['lastname'] ?? null,
                'img' => '/storage/avatar/avatar-dummy.jpg',
                'email' => $identity->email,
                'auth_sub' => $identity->sub,
                // Never used: SSO users sign in at BlubberLounge.
                'password' => Hash::make(Str::password(64)),
                'email_verified_at' => now(),
            ])->save();

            SsoUserCreated::dispatch($user, $identity);

            return $user;
        });
    }

    /**
     * Sessions that existed before the account was linked end (database
     * session driver only; "remember me" is reset by the new remember_token).
     */
    private function endOtherSessions(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $user->getKey())
            ->delete();
    }

    private function syncProfile(User $user, OidcIdentity $identity): void
    {
        if ($identity->emailVerified && $identity->email !== $user->email) {
            $emailTaken = User::where('email', $identity->email)->whereKeyNot($user->getKey())->exists();

            if (! $emailTaken) {
                $user->email = $identity->email;
            }
        }

        if ($user->isDirty()) {
            $user->save();
        }
    }
}
