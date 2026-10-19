<?php

/*
| Sign in with BlubberLounge (OAuth 2.0 / OpenID Connect), see
| App\Services\BlubberLoungeAuth. Endpoints are read from the Auth server's
| discovery document; the values below are fallbacks and overrides.
|
| The OAuth client at BlubberLounge Auth needs the redirect URI
| APP_URL/auth/blubberlounge/callback and the back-channel logout URI
| APP_URL/auth/blubberlounge/backchannel-logout.
*/

$host = rtrim((string) env('BLUBBERLOUNGE_AUTH_HOST', ''), '/');
$appUrl = rtrim((string) env('APP_URL', ''), '/');

return [

    'host' => $host,
    'client_id' => env('BLUBBERLOUNGE_AUTH_CLIENT_ID'),
    'client_secret' => env('BLUBBERLOUNGE_AUTH_CLIENT_SECRET'),
    'redirect' => env('BLUBBERLOUNGE_AUTH_REDIRECT_URI', $appUrl.'/auth/blubberlounge/callback'),
    'scopes' => env('BLUBBERLOUNGE_AUTH_SCOPES', 'openid user:read'),

    // Profile endpoint (name, email) of the Auth server, needs the user:read scope
    'user_endpoint' => env('BLUBBERLOUNGE_AUTH_USER_ENDPOINT', $host.'/api/user'),

    // OpenID Connect
    'discovery_url' => env('BLUBBERLOUNGE_AUTH_DISCOVERY_URL', $host.'/api/.well-known/openid-configuration'),
    'issuer' => env('BLUBBERLOUNGE_AUTH_ISSUER'),
    'end_session_endpoint' => env('BLUBBERLOUNGE_AUTH_END_SESSION_ENDPOINT'),
    'post_logout_redirect_uri' => env('BLUBBERLOUNGE_AUTH_POST_LOGOUT_REDIRECT_URI', $appUrl.'/'),
    'leeway' => (int) env('BLUBBERLOUNGE_AUTH_CLOCK_LEEWAY', 60),
    'cache_ttl' => (int) env('BLUBBERLOUNGE_AUTH_METADATA_CACHE_TTL', 3600),

    // Ask the user to sign in at the Auth server again on every login
    'prompt_login' => (bool) env('BLUBBERLOUNGE_AUTH_PROMPT_LOGIN', false),

];
