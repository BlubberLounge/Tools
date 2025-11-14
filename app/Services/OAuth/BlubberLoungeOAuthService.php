<?php

namespace App\Services\OAuth;

use App\Models\User;
use App\Interfaces\OAuthServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BlubberLoungeOAuthService implements OAuthServiceInterface
{
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

    public function getAuthorizationUrl(array $scopes = []): string
    {
        $scopes = empty($scopes) ? $this->defaultScopes : $scopes;
        $state = Str::random(40);
        session()->put('oauth_state', $state);

        $query = http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => $state,
            'prompt' => 'login', // "none", "consent", or "login"
        ]);

        return $this->baseUrl . '/oauth/authorize?' . $query;
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

        $accessToken = $response['access_token'];

        if (!$accessToken) {
            return response()->json(['error' => 'Access token not received', 'response' => $response], 400);
        }

        $user = Http::withToken($accessToken)
            ->get($this->apiHost . '/user')
            ->json();

        $user = response()->json($user)->getData(true);
        $user['access_token'] = $accessToken;
        $user['refresh_token'] = $response['refresh_token'];
        $user['expires_in'] = $response['expires_in'];

        return $user;
    }
}
