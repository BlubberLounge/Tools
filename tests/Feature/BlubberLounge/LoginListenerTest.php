<?php

namespace Tests\Feature\BlubberLounge;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Events\BlubberLounge\SsoLoggedIn;
use App\Models\DartPlayerInvitation;
use App\Models\User;
use App\Services\BlubberLoungeAuth\OidcIdentity;

/**
 * Tools' work after a "Sign in with BlubberLounge" login (BlubberLoungeLoginListener),
 * the routes and the login button.
 */
class LoginListenerTest extends TestCase
{
    use RefreshDatabase;

    private function identity(array $profile = [], ?string $accessToken = 'access-token'): OidcIdentity
    {
        return new OidcIdentity(
            sub: '42',
            email: 'max@example.com',
            name: 'Max',
            emailVerified: true,
            accessToken: $accessToken,
            refreshToken: 'refresh-token',
            expiresIn: 900,
            profile: $profile + ['id' => 'auth-public-id', 'firstname' => 'Max', 'lastname' => 'Mewes'],
        );
    }

    public function test_the_routes_are_registered(): void
    {
        $this->assertSame('/auth/blubberlounge/redirect', route('blubberlounge.redirect', absolute: false));
        $this->assertSame('/auth/blubberlounge/callback', route('blubberlounge.callback', absolute: false));
        $this->assertSame('/auth/blubberlounge/backchannel-logout', route('blubberlounge.backchannel-logout', absolute: false));
    }

    public function test_the_login_page_links_to_blubberlounge(): void
    {
        $this->withoutVite()
            ->get(route('login'))
            ->assertOk()
            ->assertSee(route('blubberlounge.redirect'), false);
    }

    public function test_unique_names_count_up(): void
    {
        User::factory()->create(['name' => 'Max']);
        User::factory()->create(['name' => 'Max 2']);

        $this->assertSame('Max 3', User::uniqueName('Max'));
        $this->assertSame('Anna', User::uniqueName('Anna'));
        $this->assertSame('user', User::uniqueName('  '));
    }

    public function test_a_login_stores_the_public_id_and_the_tokens(): void
    {
        $user = User::factory()->create(['email' => 'max@example.com', 'external_id' => null]);

        SsoLoggedIn::dispatch($user, $this->identity());

        $user->refresh();
        $this->assertSame('auth-public-id', $user->external_id);
        $this->assertSame('access-token', $user->access_token);
        $this->assertSame('refresh-token', $user->refresh_token);
        $this->assertTrue($user->token_expires_at->between(now()->addSeconds(890), now()->addSeconds(910)));
    }

    public function test_an_existing_public_id_is_kept(): void
    {
        $user = User::factory()->create(['email' => 'max@example.com', 'external_id' => 'already-set']);

        SsoLoggedIn::dispatch($user, $this->identity());

        $this->assertSame('already-set', $user->fresh()->external_id);
    }

    public function test_long_tokens_fit(): void
    {
        $user = User::factory()->create(['email' => 'max@example.com']);

        SsoLoggedIn::dispatch($user, $this->identity(accessToken: str_repeat('a', 1500)));

        $this->assertSame(1500, strlen($user->fresh()->access_token));
    }

    public function test_a_pending_player_invitation_is_linked(): void
    {
        $inviter = User::factory()->create();
        $user = User::factory()->create(['email' => 'max@example.com']);
        $invitation = DartPlayerInvitation::create([
            'auth_invitation_token' => 'token',
            'invited_by_user_id' => $inviter->id,
            'email' => 'max@example.com',
            'status' => 'sent',
        ]);

        SsoLoggedIn::dispatch($user, $this->identity());

        $invitation->refresh();
        $this->assertSame('registered', $invitation->status);
        $this->assertSame($user->id, $invitation->registered_user_id);
    }
}
