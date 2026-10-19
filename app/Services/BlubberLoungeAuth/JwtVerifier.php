<?php

namespace App\Services\BlubberLoungeAuth;

use App\Services\BlubberLoungeAuth\Exceptions\InvalidTokenException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

/**
 * Verifies JWTs issued by the Auth server against its JWKS.
 */
class JwtVerifier
{
    public const BACKCHANNEL_LOGOUT_EVENT = 'http://schemas.openid.net/event/backchannel-logout';

    private const ALGORITHM = 'RS256';

    public function __construct(private readonly OidcProvider $provider) {}

    /**
     * Validate an ID token (OIDC Core 3.1.3.7) and return its claims.
     *
     * @return array<string, mixed>
     *
     * @throws InvalidTokenException
     */
    public function verifyIdToken(string $jwt, ?string $expectedNonce): array
    {
        $claims = $this->decode($jwt);

        $this->assertIssuerAndAudience($claims);
        $this->assertTimestamps($claims);

        if (empty($claims['sub']) || ! is_string($claims['sub'])) {
            throw new InvalidTokenException('ID token has no subject.');
        }

        if ($expectedNonce === null || $expectedNonce === ''
            || ! is_string($claims['nonce'] ?? null)
            || ! hash_equals($expectedNonce, $claims['nonce'])) {
            throw new InvalidTokenException('ID token nonce does not match.');
        }

        return $claims;
    }

    /**
     * Validate a back-channel logout token (OIDC Back-Channel Logout 1.0, 2.6).
     *
     * @return array<string, mixed>
     *
     * @throws InvalidTokenException
     */
    public function verifyLogoutToken(string $jwt): array
    {
        $claims = $this->decode($jwt);

        $this->assertIssuerAndAudience($claims);
        $this->assertTimestamps($claims);

        if (empty($claims['jti']) || ! is_string($claims['jti'])) {
            throw new InvalidTokenException('Logout token has no jti.');
        }

        $events = $claims['events'] ?? null;

        if (! is_array($events) || ! array_key_exists(self::BACKCHANNEL_LOGOUT_EVENT, $events)
            || ! is_array($events[self::BACKCHANNEL_LOGOUT_EVENT])) {
            throw new InvalidTokenException('Logout token has no back-channel logout event.');
        }

        if (array_key_exists('nonce', $claims)) {
            throw new InvalidTokenException('Logout token must not contain a nonce.');
        }

        $sub = $claims['sub'] ?? null;
        $sid = $claims['sid'] ?? null;

        if ((! is_string($sub) || $sub === '') && (! is_string($sid) || $sid === '')) {
            throw new InvalidTokenException('Logout token has neither sub nor sid.');
        }

        return $claims;
    }

    /**
     * Check the signature and the standard time claims; return all claims.
     *
     * @return array<string, mixed>
     */
    private function decode(string $jwt): array
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            throw new InvalidTokenException('Malformed JWT.');
        }

        try {
            $header = JWT::jsonDecode(JWT::urlsafeB64Decode($parts[0]));
        } catch (Throwable $e) {
            throw new InvalidTokenException('Malformed JWT header.', 0, $e);
        }

        if (! is_object($header) || ($header->alg ?? null) !== self::ALGORITHM) {
            throw new InvalidTokenException('Unsupported JWT algorithm.');
        }

        $key = $this->resolveKey(isset($header->kid) && is_string($header->kid) ? $header->kid : null);

        $previousLeeway = JWT::$leeway;
        JWT::$leeway = $this->provider->leeway();

        try {
            $payload = JWT::decode($jwt, $key);
        } catch (Throwable $e) {
            throw new InvalidTokenException('JWT validation failed: '.$e->getMessage(), 0, $e);
        } finally {
            JWT::$leeway = $previousLeeway;
        }

        $claims = json_decode((string) json_encode($payload), true);

        if (! is_array($claims)) {
            throw new InvalidTokenException('JWT payload is not an object.');
        }

        return $claims;
    }

    private function resolveKey(?string $kid): Key
    {
        foreach ([false, true] as $refresh) {
            $keys = $this->keySet($refresh);

            if ($kid !== null && isset($keys[$kid])) {
                return $keys[$kid];
            }

            if ($kid === null && count($keys) === 1) {
                return reset($keys);
            }
        }

        throw new InvalidTokenException('No matching signing key in JWKS.');
    }

    /**
     * @return array<string, Key>
     */
    private function keySet(bool $refresh): array
    {
        $jwks = $this->provider->jwks($refresh);

        if (empty($jwks['keys'])) {
            return [];
        }

        try {
            return JWK::parseKeySet($jwks, self::ALGORITHM);
        } catch (Throwable $e) {
            throw new InvalidTokenException('JWKS could not be parsed.', 0, $e);
        }
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private function assertIssuerAndAudience(array $claims): void
    {
        if (($claims['iss'] ?? null) !== $this->provider->issuer()) {
            throw new InvalidTokenException('Unexpected issuer.');
        }

        $clientId = $this->provider->clientId();
        $audience = $claims['aud'] ?? null;
        $audiences = is_array($audience) ? $audience : [$audience];

        if (! in_array($clientId, $audiences, true)) {
            throw new InvalidTokenException('Token is not meant for this client.');
        }

        if (count($audiences) > 1 && ($claims['azp'] ?? null) !== $clientId) {
            throw new InvalidTokenException('Unexpected authorized party.');
        }
    }

    /**
     * Signature validation already rejected expired tokens and tokens from the
     * future; here we make sure the claims exist at all.
     *
     * @param  array<string, mixed>  $claims
     */
    private function assertTimestamps(array $claims): void
    {
        if (! is_numeric($claims['iat'] ?? null)) {
            throw new InvalidTokenException('Token has no iat.');
        }

        if (! is_numeric($claims['exp'] ?? null)) {
            throw new InvalidTokenException('Token has no exp.');
        }
    }
}
