<?php

namespace App\Services\OAuth;

use App\Models\User;
use App\Interfaces\OAuthServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class BlubberLoungeOAuthService implements OAuthServiceInterface
{
    /**
     * State expiration time in minutes.
     */
    protected int $stateExpirationMinutes = 10;
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected string $baseUrl;
    protected string $apiHost;
    protected array $defaultScopes = [
        'user:read',
    ];

    public function __construct()
    {
        $baseConfig = 'services.blubberlounge';
        $this->clientId = config($baseConfig . '.client_id');
        $this->clientSecret = config($baseConfig . '.client_secret');
        $this->redirectUri = config($baseConfig . '.redirect');

        $this->baseUrl = config($baseConfig . '.host');
        $this->apiHost = config($baseConfig . '.host') . '/api';
    }

    public function getAuthorizationUrl(array $scopes = [], ?string $invitationToken = null): string
    {
        $scopes = empty($scopes) ? $this->defaultScopes : $scopes;

        // Create self-contained encrypted state (no session needed)
        $state = $this->generateState();

        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => $state,
            'prompt' => 'login', // "none", "consent", or "login"
        ];

        if ($invitationToken) {
            $params['invitation'] = $invitationToken;
        }

        return $this->baseUrl . '/oauth/authorize?' . http_build_query($params);
    }

    /**
     * Generate an encrypted state token that contains its own expiration.
     */
    public function generateState(): string
    {
        $payload = [
            'nonce' => Str::random(32),
            'expires_at' => now()->addMinutes($this->stateExpirationMinutes)->timestamp,
        ];

        return base64_encode(Crypt::encryptString(json_encode($payload)));
    }

    /**
     * Verify the state token is valid and not expired.
     */
    public function verifyState(?string $state): bool
    {
        if (empty($state)) {
            return false;
        }

        try {
            $decrypted = Crypt::decryptString(base64_decode($state));
            $payload = json_decode($decrypted, true);

            if (!$payload || !isset($payload['expires_at'])) {
                return false;
            }

            return $payload['expires_at'] > now()->timestamp;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function exchangeCodeForToken(string $code): array
    {
        $response = Http::asForm()
            ->post($this->baseUrl . '/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
            ]);

        return $response->json();
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        $response = Http::asForm()
            ->withBasicAuth($this->clientId, $this->clientSecret)
            ->post($this->baseUrl . '/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);

        return $response->json();
    }

    public function ensureValidAccessToken(User $account): string
    {
        if ($account->token_expires_at && now()->lt($account->token_expires_at)) {
            return $account->access_token;
        }

        $data = $this->refreshAccessToken($account->refresh_token);

        $account->update([
            'access_token' => $data['access_token'],
            'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            'refresh_token' => $data['refresh_token'] ?? $account->refresh_token,
        ]);

        return $account->access_token;
    }

    public function user($request)
    {
        $response = $this->exchangeCodeForToken($request['code']);

        if (!isset($response['access_token'])) {
            $error = $response['error'] ?? 'unknown_error';
            $description = $response['error_description'] ?? 'Token exchange failed';
            abort(400, "OAuth Error: {$error} - {$description}");
        }

        $accessToken = $response['access_token'];

        $user = Http::withToken($accessToken)
            ->get($this->apiHost . '/user')
            ->json();

        $user = response()->json($user)->getData(true);
        $user['access_token'] = $accessToken;
        $user['refresh_token'] = $response['refresh_token'];
        $user['expires_in'] = $response['expires_in'];

        return $user;
    }

    /**
     * Validate an external OAuth access token by calling the Auth server's /api/user endpoint.
     */
    public function validateExternalToken(string $accessToken): ?array
    {
        $response = Http::withToken($accessToken)
            ->timeout(10)
            ->get($this->apiHost . '/user');

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Obtain an M2M access token via client_credentials grant.
     */
    public function getM2MToken(array $scopes = ['m2m:write']): string
    {
        $cacheKey = 'blubberlounge_m2m_token_' . md5(implode(',', $scopes));

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($scopes) {
            $response = Http::asForm()
                ->post($this->baseUrl . '/oauth/token', [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => implode(' ', $scopes),
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException(
                    'Failed to obtain M2M token: ' . ($response->json('error_description') ?? $response->body())
                );
            }

            return $response->json('access_token');
        });
    }

    /**
     * Call the Auth server's invitation API to create an invitation.
     */
    public function createInvitation(array $data): array
    {
        $token = $this->getM2MToken(['m2m:write']);

        $response = Http::withToken($token)
            ->post($this->apiHost . '/invitations', $data);

        return $response->json();
    }

    /**
     * Check invitation status on the Auth server.
     */
    public function getInvitationStatus(string $invitationToken): array
    {
        $token = $this->getM2MToken(['m2m:read']);

        $response = Http::withToken($token)
            ->timeout(10)
            ->get($this->apiHost . '/invitations/' . $invitationToken . '/status');

        if (!$response->successful()) {
            throw new \RuntimeException(
                'Auth server invitation status check failed: ' . $response->status()
            );
        }

        return $response->json();
    }
}
