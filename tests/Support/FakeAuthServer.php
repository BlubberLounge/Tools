<?php

namespace Tests\Support;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

/**
 * A fake BlubberLounge Auth server for Http::fake(): discovery, JWKS and
 * RS256 signed tokens with a freshly generated key pair.
 */
class FakeAuthServer
{
    public const HOST = 'https://auth.test';

    public const CLIENT_ID = 'test-client-id';

    public const KID = 'test-key-1';

    private static ?array $keyPair = null;

    /**
     * @return array{private: string, public: string, n: string, e: string}
     */
    public static function keyPair(): array
    {
        if (self::$keyPair === null) {
            $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
            openssl_pkey_export($key, $private);
            $details = openssl_pkey_get_details($key);

            self::$keyPair = [
                'private' => $private,
                'public' => $details['key'],
                'n' => self::b64($details['rsa']['n']),
                'e' => self::b64($details['rsa']['e']),
            ];
        }

        return self::$keyPair;
    }

    /**
     * A second, unrelated key pair (for "wrong signature" tests).
     */
    public static function otherPrivateKey(): string
    {
        static $private = null;

        if ($private === null) {
            $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
            openssl_pkey_export($key, $private);
        }

        return $private;
    }

    public static function configure(): void
    {
        config([
            'blubberlounge-auth.host' => self::HOST,
            'blubberlounge-auth.client_id' => self::CLIENT_ID,
            'blubberlounge-auth.client_secret' => 'test-secret',
            'blubberlounge-auth.redirect' => 'http://localhost/auth/blubberlounge/callback',
            'blubberlounge-auth.scopes' => 'openid user:read',
            'blubberlounge-auth.user_endpoint' => self::HOST.'/api/user',
            'blubberlounge-auth.discovery_url' => self::HOST.'/api/.well-known/openid-configuration',
            'blubberlounge-auth.issuer' => null,
            'blubberlounge-auth.end_session_endpoint' => null,
            'blubberlounge-auth.post_logout_redirect_uri' => 'http://localhost/login',
        ]);
    }

    /**
     * @param  array<string, mixed>  $discoveryOverrides
     * @return array<string, mixed>
     */
    public static function discovery(array $discoveryOverrides = []): array
    {
        return array_merge([
            'issuer' => self::HOST,
            'authorization_endpoint' => self::HOST.'/oauth/authorize',
            'token_endpoint' => self::HOST.'/oauth/token',
            'userinfo_endpoint' => self::HOST.'/api/oauth/userinfo',
            'jwks_uri' => self::HOST.'/api/oauth/jwks',
        ], $discoveryOverrides);
    }

    /**
     * @return array<string, mixed>
     */
    public static function jwks(): array
    {
        $pair = self::keyPair();

        return ['keys' => [[
            'kty' => 'RSA',
            'alg' => 'RS256',
            'use' => 'sig',
            'kid' => self::KID,
            'n' => $pair['n'],
            'e' => $pair['e'],
        ]]];
    }

    /**
     * Fake the metadata endpoints plus any extra routes.
     *
     * @param  array<string, mixed>  $extra
     * @param  array<string, mixed>  $discoveryOverrides
     */
    public static function fake(array $extra = [], array $discoveryOverrides = []): void
    {
        Http::preventStrayRequests();

        Http::fake(array_merge($extra, [
            self::HOST.'/api/.well-known/openid-configuration' => Http::response(self::discovery($discoveryOverrides)),
            self::HOST.'/api/oauth/jwks' => Http::response(self::jwks()),
        ]));
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    public static function sign(array $claims, ?string $kid = self::KID, ?string $privateKey = null, array $header = []): string
    {
        return JWT::encode($claims, $privateKey ?? self::keyPair()['private'], 'RS256', $kid, $header);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    public static function idToken(string $nonce, array $overrides = []): string
    {
        return self::sign(array_merge([
            'iss' => self::HOST,
            'aud' => self::CLIENT_ID,
            'sub' => '42',
            'iat' => time(),
            'exp' => time() + 300,
            'nonce' => $nonce,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    public static function logoutToken(array $overrides = [], array $header = ['typ' => 'logout+jwt']): string
    {
        $claims = array_merge([
            'iss' => self::HOST,
            'aud' => self::CLIENT_ID,
            'sub' => '42',
            'iat' => time(),
            'exp' => time() + 120,
            'jti' => bin2hex(random_bytes(16)),
            'events' => ['http://schemas.openid.net/event/backchannel-logout' => new \stdClass],
        ], $overrides);

        return self::sign(array_filter($claims, fn ($value) => $value !== null), self::KID, null, $header);
    }

    private static function b64(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
