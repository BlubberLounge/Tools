<?php

namespace App\Interfaces;

use App\Models\User;

interface OAuthServiceInterface
{
    /**
     * Return the OAuth authorization URL for this service.
     */
    public function getAuthorizationUrl(array $scopes = []): string;

    /**
     * Exchange authorization code for tokens.
     */
    public function exchangeCodeForToken(string $code): array;

    /**
     * Refresh an expired access token.
     */
    public function refreshAccessToken(string $refreshToken): array;

    /**
     * Ensure and return a valid access token, refreshing if necessary.
     */
    public function ensureValidAccessToken(User $account): string;
}
