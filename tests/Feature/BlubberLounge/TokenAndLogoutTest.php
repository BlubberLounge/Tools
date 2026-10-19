<?php

namespace Tests\Feature\BlubberLounge;

use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Tests\Support\FakeAuthServer as Auth;
use Tests\TestCase;

use App\Models\OidcSession;
use App\Models\User;
use App\Services\BlubberLoungeAuth\Exceptions\InvalidTokenException;
use App\Services\BlubberLoungeAuth\JwtVerifier;
use App\Services\BlubberLoungeAuth\OidcSessionManager;

/**
 * ID token validation, back-channel logout and logout at the Auth server.
 */
class TokenAndLogoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Auth::configure();
    }

    private function user(string $email, ?string $sub = null): User
    {
        $user = User::factory()->create(['email' => $email, 'email_verified_at' => now()]);
        $user->forceFill(['auth_sub' => $sub, 'remember_token' => 'remember-me'])->save();

        return $user;
    }

    // --- ID tokens -----------------------------------------------------------

    public function test_a_valid_id_token_is_accepted(): void
    {
        Auth::fake();

        $this->assertSame('42', app(JwtVerifier::class)->verifyIdToken(Auth::idToken('n-1'), 'n-1')['sub']);
    }

    public static function invalidIdTokens(): array
    {
        $claims = fn () => ['iss' => Auth::HOST, 'aud' => Auth::CLIENT_ID, 'sub' => '42', 'iat' => time(), 'exp' => time() + 60, 'nonce' => 'n-1'];

        return [
            'wrong signature' => [fn () => Auth::sign($claims(), Auth::KID, Auth::otherPrivateKey())],
            'wrong issuer' => [fn () => Auth::idToken('n-1', ['iss' => 'https://evil.test'])],
            'wrong audience' => [fn () => Auth::idToken('n-1', ['aud' => 'other-client'])],
            'expired' => [fn () => Auth::idToken('n-1', ['iat' => time() - 3600, 'exp' => time() - 600])],
            'nonce mismatch' => [fn () => Auth::idToken('other')],
            'unknown kid' => [fn () => Auth::sign($claims(), 'unknown-kid')],
            'not a JWT' => [fn () => 'abc.def'],
        ];
    }

    #[DataProvider('invalidIdTokens')]
    public function test_invalid_id_tokens_are_rejected(Closure $token): void
    {
        Auth::fake();
        $this->expectException(InvalidTokenException::class);

        app(JwtVerifier::class)->verifyIdToken($token(), 'n-1');
    }

    // --- back-channel logout ---------------------------------------------------

    /**
     * SSO session mappings (Tools stores sessions in files, the handler deletes them).
     */
    private function sessionsFor(User $user, User $other): void
    {
        OidcSession::create(['session_id' => 's-user-1', 'sid' => 'auth-sid-1', 'user_id' => $user->id]);
        OidcSession::create(['session_id' => 's-user-2', 'sid' => 'auth-sid-2', 'user_id' => $user->id]);
        OidcSession::create(['session_id' => 's-other', 'sid' => 'auth-sid-3', 'user_id' => $other->id]);
    }

    /**
     * @return array<int, string>
     */
    private function remainingSessions(): array
    {
        return OidcSession::orderBy('session_id')->pluck('session_id')->all();
    }

    private function postLogoutToken(?string $token): TestResponse
    {
        return $this->post('/auth/blubberlounge/backchannel-logout', $token === null ? [] : ['logout_token' => $token]);
    }

    public function test_a_logout_token_for_a_subject_ends_all_sessions_of_that_user(): void
    {
        Auth::fake();
        $user = $this->user('max@example.com', '42');
        $this->sessionsFor($user, $this->user('other@example.com'));

        $this->postLogoutToken(Auth::logoutToken())->assertOk();

        $this->assertSame(['s-other'], $this->remainingSessions());
        $this->assertNotSame('remember-me', $user->fresh()->remember_token);
    }

    public function test_a_logout_token_with_sid_ends_only_that_session(): void
    {
        Auth::fake();
        $this->sessionsFor($this->user('max@example.com', '42'), $this->user('other@example.com'));

        $this->postLogoutToken(Auth::logoutToken(['sid' => 'auth-sid-1']))->assertOk();

        $this->assertSame(['s-other', 's-user-2'], $this->remainingSessions());
    }

    public function test_replayed_and_invalid_logout_tokens_are_rejected(): void
    {
        Auth::fake();
        $token = Auth::logoutToken(['sid' => 'auth-sid-1']);

        $this->postLogoutToken($token)->assertOk();
        $this->postLogoutToken($token)->assertStatus(400);
        $this->postLogoutToken(null)->assertStatus(400);
        $this->postLogoutToken(Auth::logoutToken(['aud' => 'other-client']))->assertStatus(400);
        $this->postLogoutToken(Auth::logoutToken(['nonce' => 'n']))->assertStatus(400);
        $this->postLogoutToken(Auth::idToken('n-1'))->assertStatus(400);
    }

    public function test_the_back_channel_endpoint_needs_no_csrf_token_and_sets_no_cookies(): void
    {
        Auth::fake();

        $response = $this->postLogoutToken(Auth::logoutToken());

        $response->assertOk();
        $this->assertEmpty($response->headers->getCookies());
    }

    // --- logout ----------------------------------------------------------------

    public function test_an_sso_session_is_ended_at_the_auth_server(): void
    {
        Auth::fake(discoveryOverrides: ['end_session_endpoint' => Auth::HOST.'/oauth/logout']);

        $response = $this->actingAs($this->user('max@example.com', '42'))
            ->withSession([OidcSessionManager::SUB_KEY => '42', OidcSessionManager::ID_TOKEN_KEY => 'the-id-token'])
            ->post('/logout');

        $this->assertGuest();
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith(Auth::HOST.'/oauth/logout?', $location);
        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame(Auth::CLIENT_ID, $query['client_id']);
        $this->assertSame('the-id-token', $query['id_token_hint']);
    }

    public function test_a_password_session_only_logs_out_locally(): void
    {
        Auth::fake(discoveryOverrides: ['end_session_endpoint' => Auth::HOST.'/oauth/logout']);

        $this->actingAs($this->user('max@example.com'))->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }
}
