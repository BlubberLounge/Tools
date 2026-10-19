<?php

namespace App\Services\BlubberLoungeAuth;

use App\Services\BlubberLoungeAuth\Exceptions\InvalidTokenException;
use App\Services\BlubberLoungeAuth\Exceptions\OAuthLoginException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Authorization code flow with PKCE (S256) and OpenID Connect against
 * BlubberLounge Auth.
 *
 * The subject (sub) comes from the id_token when the Auth server returns one,
 * otherwise from the userinfo endpoint. Name and email come from the Auth
 * server's /api/user endpoint (scope user:read).
 */
class OAuthService
{
    public const FLOW_SESSION_KEY = 'blubberlounge-auth.flow';

    public function __construct(
        private readonly OidcProvider $provider,
        private readonly JwtVerifier $verifier,
    ) {}

    public function redirect(Request $request): RedirectResponse
    {
        $state = Str::random(40);
        $nonce = Str::random(40);
        $codeVerifier = Str::random(96);

        $request->session()->put(self::FLOW_SESSION_KEY, [
            'state' => $state,
            'nonce' => $nonce,
            'code_verifier' => $codeVerifier,
        ]);

        $query = array_filter([
            'client_id' => $this->provider->clientId(),
            'redirect_uri' => $this->provider->redirectUri(),
            'response_type' => 'code',
            'scope' => $this->provider->scopes(),
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => self::codeChallenge($codeVerifier),
            'code_challenge_method' => 'S256',
            'prompt' => config('blubberlounge-auth.prompt_login') ? 'login' : null,
        ]);

        $endpoint = $this->provider->authorizationEndpoint();
        $separator = str_contains($endpoint, '?') ? '&' : '?';

        return redirect()->away($endpoint.$separator.http_build_query($query, '', '&', PHP_QUERY_RFC3986));
    }

    /**
     * Complete the authorization code flow.
     *
     * @throws OAuthLoginException with a message that is safe to show to the user
     */
    public function handleCallback(Request $request): OidcIdentity
    {
        $flow = $request->session()->pull(self::FLOW_SESSION_KEY);

        if ($request->filled('error')) {
            $error = (string) $request->query('error');

            throw new OAuthLoginException(
                $error === 'access_denied'
                    ? 'Sign-in with BlubberLounge was cancelled.'
                    : 'Sign-in with BlubberLounge failed. Please try again.',
                $error
            );
        }

        $state = $request->query('state');

        if (! is_array($flow) || ! is_string($state) || $state === ''
            || ! is_string($flow['state'] ?? null) || ! hash_equals($flow['state'], $state)) {
            throw new OAuthLoginException('Your sign-in request has expired. Please try again.', 'invalid_state');
        }

        $code = $request->query('code');

        if (! is_string($code) || $code === '') {
            throw new OAuthLoginException('Sign-in with BlubberLounge failed. Please try again.', 'missing_code');
        }

        $tokens = $this->exchangeCode($code, (string) $flow['code_verifier']);
        $accessToken = $tokens['access_token'];
        $idToken = is_string($tokens['id_token'] ?? null) && $tokens['id_token'] !== '' ? $tokens['id_token'] : null;
        $sid = null;

        if ($idToken !== null) {
            try {
                $claims = $this->verifier->verifyIdToken($idToken, $flow['nonce'] ?? null);
            } catch (InvalidTokenException $e) {
                throw new OAuthLoginException('Sign-in with BlubberLounge failed. Please try again.', 'invalid_id_token', $e);
            }

            $sub = $claims['sub'];
            $sid = is_string($claims['sid'] ?? null) && $claims['sid'] !== '' ? $claims['sid'] : null;
        } else {
            $sub = $this->fetchSubject($accessToken);
        }

        $user = $this->fetchProfile($accessToken);
        $email = $user['email'] ?? null;

        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new OAuthLoginException('Your BlubberLounge account has no usable email address.', 'missing_email');
        }

        return new OidcIdentity(
            sub: $sub,
            email: $email,
            name: is_string($user['name'] ?? null) && $user['name'] !== '' ? $user['name'] : Str::before($email, '@'),
            emailVerified: ! empty($user['email_verified_at']) || ($user['email_verified'] ?? false) === true,
            idToken: $idToken,
            sid: $sid,
            accessToken: $accessToken,
            refreshToken: is_string($tokens['refresh_token'] ?? null) ? $tokens['refresh_token'] : null,
            expiresIn: is_numeric($tokens['expires_in'] ?? null) ? (int) $tokens['expires_in'] : null,
            profile: $user,
        );
    }

    public static function codeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    /**
     * @return array{access_token: string, id_token?: string, refresh_token?: string, expires_in?: int}
     */
    private function exchangeCode(string $code, string $codeVerifier): array
    {
        try {
            $response = Http::asForm()->acceptJson()->timeout(10)->post($this->provider->tokenEndpoint(), [
                'grant_type' => 'authorization_code',
                'client_id' => $this->provider->clientId(),
                'client_secret' => $this->provider->clientSecret(),
                'redirect_uri' => $this->provider->redirectUri(),
                'code' => $code,
                'code_verifier' => $codeVerifier,
            ]);
        } catch (Throwable $e) {
            throw new OAuthLoginException('BlubberLounge could not be reached. Please try again later.', 'token_request_failed', $e);
        }

        $accessToken = $response->json('access_token');

        if (! $response->successful() || ! is_string($accessToken) || $accessToken === '') {
            Log::warning('OAuth token exchange failed.', [
                'status' => $response->status(),
                'error' => $response->json('error'),
            ]);

            throw new OAuthLoginException('Sign-in with BlubberLounge failed. Please try again.', 'token_exchange_failed');
        }

        return $response->json();
    }

    private function fetchSubject(string $accessToken): string
    {
        $claims = $this->getJson($this->provider->userinfoEndpoint(), $accessToken, 'userinfo');
        $sub = $claims['sub'] ?? null;

        if (! is_string($sub) && ! is_int($sub)) {
            throw new OAuthLoginException('Sign-in with BlubberLounge failed. Please try again.', 'userinfo_without_sub');
        }

        return (string) $sub;
    }

    /**
     * The Auth server's user profile (name, email, email_verified_at, firstname, ...).
     *
     * @return array<string, mixed>
     */
    private function fetchProfile(string $accessToken): array
    {
        $user = $this->getJson($this->provider->userEndpoint(), $accessToken, 'user');

        // Laravel API resources may wrap the payload in "data"
        if (isset($user['data']) && is_array($user['data'])) {
            $user = $user['data'];
        }

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function getJson(string $url, string $accessToken, string $what): array
    {
        try {
            $response = Http::acceptJson()->withToken($accessToken)->timeout(10)->get($url);
        } catch (Throwable $e) {
            throw new OAuthLoginException('BlubberLounge could not be reached. Please try again later.', "{$what}_request_failed", $e);
        }

        $json = $response->json();

        if (! $response->successful() || ! is_array($json)) {
            Log::warning("OAuth {$what} request failed.", ['status' => $response->status()]);

            throw new OAuthLoginException('Sign-in with BlubberLounge failed. Please try again.', "{$what}_request_failed");
        }

        return $json;
    }
}
