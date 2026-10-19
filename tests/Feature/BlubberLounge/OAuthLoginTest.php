<?php

namespace Tests\Feature\BlubberLounge;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\Support\FakeAuthServer as Auth;
use Tests\TestCase;

use App\Events\BlubberLounge\SsoLoggedIn;
use App\Events\BlubberLounge\SsoUserCreated;
use App\Models\OidcSession;
use App\Models\User;
use App\Services\BlubberLoungeAuth\OAuthService;
use App\Services\BlubberLoungeAuth\OidcSessionManager;

class OAuthLoginTest extends TestCase
{
    use RefreshDatabase;

    private const FLOW = ['state' => 'state-123', 'nonce' => 'nonce-123', 'code_verifier' => 'verifier-123'];

    protected function setUp(): void
    {
        parent::setUp();
        Auth::configure();
    }

    private function user(string $email, ?string $sub = null, bool $verified = true): User
    {
        $user = User::factory()->create(['email' => $email, 'email_verified_at' => $verified ? now() : null]);
        $user->forceFill(['auth_sub' => $sub, 'remember_token' => 'remember-me'])->save();

        return $user;
    }

    /**
     * Fake token, profile and userinfo endpoints of the Auth server.
     */
    private function fakeAuthLogin(array $token = [], array $profile = [], array $userinfo = ['sub' => '42'], int $tokenStatus = 200, int $profileStatus = 200): void
    {
        Auth::fake([
            Auth::HOST.'/oauth/token' => Http::response(array_merge(['access_token' => 'access-token', 'token_type' => 'Bearer'], $token), $tokenStatus),
            Auth::HOST.'/api/user' => Http::response(array_merge([
                'id' => 'auth-public-id',
                'name' => 'Max',
                'firstname' => 'Max',
                'lastname' => 'Mewes',
                'email' => 'max@example.com',
                'email_verified_at' => '2025-01-01T00:00:00Z',
            ], $profile), $profileStatus),
            Auth::HOST.'/api/oauth/userinfo' => Http::response($userinfo),
        ]);
    }

    private function oauthCallback(array $query = []): TestResponse
    {
        $query = array_filter(array_merge(['code' => 'auth-code', 'state' => self::FLOW['state']], $query), fn ($value) => $value !== null);

        return $this->withSession([OAuthService::FLOW_SESSION_KEY => self::FLOW])
            ->get('/auth/blubberlounge/callback?'.http_build_query($query));
    }

    public function test_redirect_sends_pkce_nonce_state_and_the_openid_scope(): void
    {
        Auth::fake();

        $location = $this->get('/auth/blubberlounge/redirect')->headers->get('Location');
        $this->assertStringStartsWith(Auth::HOST.'/oauth/authorize?', $location);
        parse_str(parse_url($location, PHP_URL_QUERY), $query);

        $flow = session(OAuthService::FLOW_SESSION_KEY);
        $this->assertSame('openid user:read', $query['scope']);
        $this->assertSame($flow['state'], $query['state']);
        $this->assertSame($flow['nonce'], $query['nonce']);
        $this->assertSame('S256', $query['code_challenge_method']);
        $this->assertSame(OAuthService::codeChallenge($flow['code_verifier']), $query['code_challenge']);
        $this->assertSame(Auth::CLIENT_ID, $query['client_id']);
    }

    public function test_login_creates_the_user_and_stores_the_session_data(): void
    {
        $this->fakeAuthLogin(['id_token' => Auth::idToken(self::FLOW['nonce'], ['sid' => 'auth-session-1']), 'refresh_token' => 'refresh', 'expires_in' => 900]);

        $this->oauthCallback()->assertRedirect(route('home'));

        $user = User::where('auth_sub', '42')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('max@example.com', $user->email);
        $this->assertSame('Max', $user->firstname);
        $this->assertSame('Mewes', $user->lastname);
        $this->assertSame('/storage/avatar/avatar-dummy.jpg', $user->img);
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse(Hash::check('Max', $user->password));
        $this->assertSame('auth-session-1', session(OidcSessionManager::SID_KEY));
        $this->assertTrue(OidcSession::where('sid', 'auth-session-1')->where('user_id', $user->id)->exists());

        // BlubberLoungeLoginListener: public id and tokens for the dart API
        $this->assertSame('auth-public-id', $user->external_id);
        $this->assertSame('access-token', $user->access_token);
        $this->assertSame('refresh', $user->refresh_token);

        Http::assertSent(fn (HttpRequest $request) => $request->url() === Auth::HOST.'/oauth/token'
            && $request['code_verifier'] === self::FLOW['code_verifier']
            && $request['client_secret'] === 'test-secret');
        Http::assertNotSent(fn (HttpRequest $request) => str_contains($request->url(), '/userinfo'));
    }

    public function test_new_users_get_a_unique_name(): void
    {
        User::factory()->create(['name' => 'Max']);
        $this->fakeAuthLogin(['id_token' => Auth::idToken(self::FLOW['nonce'])]);

        $this->oauthCallback()->assertRedirect(route('home'));

        $this->assertSame('Max 2', User::where('auth_sub', '42')->value('name'));
    }

    public function test_login_events_are_dispatched(): void
    {
        Event::fake([SsoUserCreated::class, SsoLoggedIn::class]);
        $this->fakeAuthLogin(['id_token' => Auth::idToken(self::FLOW['nonce'])]);

        $this->oauthCallback();

        Event::assertDispatched(SsoUserCreated::class);
        Event::assertDispatched(SsoLoggedIn::class, fn (SsoLoggedIn $e) => $e->identity->accessToken === 'access-token');
    }

    public function test_login_without_id_token_reads_the_subject_from_userinfo(): void
    {
        $this->fakeAuthLogin(userinfo: ['sub' => '77']);

        $this->oauthCallback()->assertRedirect(route('home'));

        $this->assertAuthenticatedAs(User::where('auth_sub', '77')->firstOrFail());
    }

    public function test_an_id_token_with_a_wrong_nonce_is_rejected(): void
    {
        $this->fakeAuthLogin(['id_token' => Auth::idToken('other-nonce')]);

        $this->oauthCallback()->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertGuest();
    }

    public function test_a_bad_state_returns_to_the_login_page_without_calling_the_auth_server(): void
    {
        $this->fakeAuthLogin();

        $this->oauthCallback(['state' => 'forged'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your sign-in request has expired. Please try again.');

        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_denied_consent_returns_to_the_login_page(): void
    {
        $this->fakeAuthLogin();

        $this->oauthCallback(['error' => 'access_denied', 'code' => null])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Sign-in with BlubberLounge was cancelled.');
    }

    public function test_a_failed_token_exchange_returns_to_the_login_page(): void
    {
        $this->fakeAuthLogin(['error' => 'invalid_grant'], tokenStatus: 400);

        $this->oauthCallback()->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertGuest();
    }

    public function test_an_unreachable_auth_server_returns_to_the_login_page(): void
    {
        Http::fake([Auth::HOST.'/*' => Http::failedConnection()]);

        $this->oauthCallback()->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertGuest();
    }

    public function test_a_known_subject_logs_in_its_own_user_and_keeps_its_name(): void
    {
        $linked = $this->user('old@example.com', '42');
        $name = $linked->name;

        $this->fakeAuthLogin(['id_token' => Auth::idToken(self::FLOW['nonce'])]);
        $this->oauthCallback()->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($linked);
        $this->assertSame('max@example.com', $linked->fresh()->email);
        $this->assertSame($name, $linked->fresh()->name);
    }

    public function test_an_email_change_to_a_taken_address_is_not_applied(): void
    {
        $linked = $this->user('old@example.com', '42');
        $other = $this->user('max@example.com');

        $this->fakeAuthLogin(['id_token' => Auth::idToken(self::FLOW['nonce'])]);
        $this->oauthCallback();

        $this->assertAuthenticatedAs($linked);
        $this->assertSame('old@example.com', $linked->fresh()->email);
        $this->assertNull($other->fresh()->auth_sub);
    }

    public function test_an_existing_verified_user_is_linked_by_email(): void
    {
        $user = $this->user('max@example.com');

        $this->fakeAuthLogin(['id_token' => Auth::idToken(self::FLOW['nonce'])]);
        $this->oauthCallback()->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('42', $user->fresh()->auth_sub);
        $this->assertNotSame('remember-me', $user->fresh()->remember_token);
    }

    public function test_a_locally_unverified_account_is_not_linked(): void
    {
        $user = $this->user('max@example.com', verified: false);

        $this->fakeAuthLogin(['id_token' => Auth::idToken(self::FLOW['nonce'])]);
        $this->oauthCallback()->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertGuest();
        $this->assertNull($user->fresh()->auth_sub);
    }

    public function test_a_different_subject_cannot_take_over_an_already_linked_account(): void
    {
        $user = $this->user('max@example.com', '42');

        $this->fakeAuthLogin(['id_token' => Auth::idToken(self::FLOW['nonce'], ['sub' => '99'])]);
        $this->oauthCallback()->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertGuest();
        $this->assertSame('42', $user->fresh()->auth_sub);
    }

    public function test_no_account_is_created_for_an_unverified_email(): void
    {
        $before = User::count();
        $this->fakeAuthLogin(profile: ['email_verified_at' => null]);

        $this->oauthCallback()->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertSame($before, User::count());
    }
}
