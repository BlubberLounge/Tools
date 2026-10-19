<?php

namespace App\Http\Controllers\Auth;

use App\Services\BlubberLoungeAuth\Exceptions\InvalidTokenException;
use App\Services\BlubberLoungeAuth\JwtVerifier;
use App\Services\BlubberLoungeAuth\OidcSessionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * OpenID Connect Back-Channel Logout 1.0 endpoint.
 *
 * The Auth server POSTs a signed logout_token when a user signs out there;
 * the matching local sessions are deleted. Extends Laravel's base controller,
 * not App\Http\Controllers\Controller, which requires a logged-in user.
 */
class BlubberLoungeBackChannelLogoutController extends Controller
{
    public function __invoke(Request $request, JwtVerifier $verifier, OidcSessionManager $oidcSessions): JsonResponse
    {
        $token = $request->input('logout_token');

        if (! is_string($token) || $token === '') {
            return $this->error('Missing logout_token.');
        }

        try {
            $claims = $verifier->verifyLogoutToken($token);
        } catch (InvalidTokenException $e) {
            Log::warning('Rejected back-channel logout token.', ['reason' => $e->getMessage()]);

            return $this->error('Invalid logout_token.');
        }

        // Reject replays for as long as the token could still be accepted.
        $ttl = max(60, (int) $claims['exp'] - time() + 2 * (int) config('blubberlounge-auth.leeway', 60));

        if (! Cache::add('blubberlounge-auth:logout_jti:'.sha1($claims['jti']), true, $ttl)) {
            Log::warning('Rejected replayed back-channel logout token.');

            return $this->error('Replayed logout_token.');
        }

        $sub = is_string($claims['sub'] ?? null) && $claims['sub'] !== '' ? $claims['sub'] : null;
        $sid = is_string($claims['sid'] ?? null) && $claims['sid'] !== '' ? $claims['sid'] : null;

        $ended = $oidcSessions->logout($sub, $sid);

        Log::info('Back-channel logout processed.', ['sessions' => $ended]);

        return response()->json(new \stdClass, 200, ['Cache-Control' => 'no-store']);
    }

    private function error(string $description): JsonResponse
    {
        return response()->json(
            ['error' => 'invalid_request', 'error_description' => $description],
            400,
            ['Cache-Control' => 'no-store']
        );
    }
}
