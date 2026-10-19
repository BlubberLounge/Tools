<?php

namespace App\Services\BlubberLoungeAuth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Metadata of the BlubberLounge Auth server: discovery document and JWKS.
 *
 * Both are cached. Missing discovery values fall back to the endpoints the
 * Auth server has always used, so plain OAuth logins keep working when the
 * discovery document cannot be loaded.
 */
class OidcProvider
{
    private const DISCOVERY_CACHE_KEY = 'blubberlounge-auth:discovery';

    private const JWKS_CACHE_KEY = 'blubberlounge-auth:jwks';

    /**
     * @var array<string, mixed>|null
     */
    private ?array $discovery = null;

    public function clientId(): string
    {
        return (string) config('blubberlounge-auth.client_id');
    }

    public function clientSecret(): string
    {
        return (string) config('blubberlounge-auth.client_secret');
    }

    public function redirectUri(): string
    {
        return (string) config('blubberlounge-auth.redirect');
    }

    public function scopes(): string
    {
        return (string) config('blubberlounge-auth.scopes');
    }

    public function leeway(): int
    {
        return (int) config('blubberlounge-auth.leeway', 60);
    }

    public function issuer(): string
    {
        return (string) (config('blubberlounge-auth.issuer')
            ?? $this->discovery()['issuer']
            ?? $this->host());
    }

    public function authorizationEndpoint(): string
    {
        return (string) ($this->discovery()['authorization_endpoint'] ?? $this->host().'/oauth/authorize');
    }

    public function tokenEndpoint(): string
    {
        return (string) ($this->discovery()['token_endpoint'] ?? $this->host().'/oauth/token');
    }

    public function userinfoEndpoint(): string
    {
        return (string) ($this->discovery()['userinfo_endpoint'] ?? $this->host().'/api/oauth/userinfo');
    }

    public function userEndpoint(): string
    {
        return (string) config('blubberlounge-auth.user_endpoint');
    }

    public function jwksUri(): string
    {
        return (string) ($this->discovery()['jwks_uri'] ?? $this->host().'/api/oauth/jwks');
    }

    public function endSessionEndpoint(): ?string
    {
        $endpoint = config('blubberlounge-auth.end_session_endpoint')
            ?? $this->discovery()['end_session_endpoint']
            ?? null;

        return is_string($endpoint) && $endpoint !== '' ? $endpoint : null;
    }

    public function postLogoutRedirectUri(): string
    {
        return (string) config('blubberlounge-auth.post_logout_redirect_uri');
    }

    /**
     * The JSON Web Key Set of the Auth server.
     *
     * @return array{keys?: array<int, array<string, mixed>>}
     */
    public function jwks(bool $refresh = false): array
    {
        if ($refresh) {
            Cache::forget(self::JWKS_CACHE_KEY);
        }

        $jwks = Cache::get(self::JWKS_CACHE_KEY);

        if (is_array($jwks)) {
            return $jwks;
        }

        $jwks = $this->fetchJson($this->jwksUri());

        if (! is_array($jwks) || ! isset($jwks['keys']) || ! is_array($jwks['keys'])) {
            return ['keys' => []];
        }

        Cache::put(self::JWKS_CACHE_KEY, $jwks, $this->cacheTtl());

        return $jwks;
    }

    /**
     * @return array<string, mixed>
     */
    public function discovery(): array
    {
        if ($this->discovery !== null) {
            return $this->discovery;
        }

        $cached = Cache::get(self::DISCOVERY_CACHE_KEY);

        if (is_array($cached)) {
            return $this->discovery = $cached;
        }

        $url = (string) config('blubberlounge-auth.discovery_url');
        $document = $url !== '' ? $this->fetchJson($url) : null;

        if (! is_array($document)) {
            // Do not cache failures, but avoid refetching within this request.
            return $this->discovery = [];
        }

        Cache::put(self::DISCOVERY_CACHE_KEY, $document, $this->cacheTtl());

        return $this->discovery = $document;
    }

    private function host(): string
    {
        return rtrim((string) config('blubberlounge-auth.host'), '/');
    }

    private function cacheTtl(): int
    {
        return max(60, (int) config('blubberlounge-auth.cache_ttl', 3600));
    }

    private function fetchJson(string $url): ?array
    {
        try {
            $response = Http::acceptJson()->timeout(5)->get($url);
        } catch (Throwable $e) {
            Log::warning('OIDC metadata request failed.', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('OIDC metadata request returned an error.', ['url' => $url, 'status' => $response->status()]);

            return null;
        }

        $json = $response->json();

        return is_array($json) ? $json : null;
    }
}
