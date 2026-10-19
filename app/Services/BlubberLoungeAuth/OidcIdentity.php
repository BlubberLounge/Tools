<?php

namespace App\Services\BlubberLoungeAuth;

/**
 * The user as reported by the Auth server after a successful login.
 */
final readonly class OidcIdentity
{
    /**
     * @param  array<string, mixed>  $profile  the Auth server's /api/user payload (firstname, lastname, ...)
     */
    public function __construct(
        public string $sub,
        public string $email,
        public string $name,
        public bool $emailVerified,
        public ?string $idToken = null,
        public ?string $sid = null,
        public ?string $accessToken = null,
        public ?string $refreshToken = null,
        public ?int $expiresIn = null,
        public array $profile = [],
    ) {}
}
