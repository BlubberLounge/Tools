<?php

namespace App\Services\BlubberLoungeAuth;

use App\Models\OidcSession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Keeps track of sessions started by single sign-on, ends them on
 * back-channel logout and signs users out (RP-initiated logout).
 */
class OidcSessionManager
{
    public const ID_TOKEN_KEY = 'blubberlounge-auth.id_token';

    public const SID_KEY = 'blubberlounge-auth.sid';

    public const SUB_KEY = 'blubberlounge-auth.sub';

    public function __construct(
        private readonly SessionManager $sessions,
        private readonly OidcProvider $provider,
    ) {}

    /**
     * Remember the OIDC data of a fresh login in the (already regenerated) session.
     */
    public function register(Request $request, User $user, OidcIdentity $identity): void
    {
        $session = $request->session();

        $session->put(self::SUB_KEY, $identity->sub);

        if ($identity->idToken !== null) {
            $session->put(self::ID_TOKEN_KEY, $identity->idToken);
        }

        if ($identity->sid !== null) {
            $session->put(self::SID_KEY, $identity->sid);
        }

        OidcSession::create([
            'session_id' => $session->getId(),
            'sid' => $identity->sid,
            'user_id' => $user->getKey(),
        ]);
    }

    /**
     * The local session ended (logout); drop its mapping.
     */
    public function forget(string $sessionId): void
    {
        OidcSession::where('session_id', $sessionId)->delete();
    }

    /**
     * Sign the current user out. Sessions started by single sign-on are also
     * ended at the Auth server (RP-initiated logout) when it offers an
     * end_session_endpoint; everyone else goes to $fallback.
     */
    public function logoutResponse(Request $request, string $fallback = '/'): RedirectResponse
    {
        $session = $request->session();
        $isSso = $session->has(self::SUB_KEY);
        $idToken = $session->get(self::ID_TOKEN_KEY);

        $this->forget($session->getId());

        Auth::logout();

        $session->invalidate();
        $session->regenerateToken();

        $endSessionEndpoint = $isSso ? $this->provider->endSessionEndpoint() : null;

        if ($endSessionEndpoint === null) {
            return redirect($fallback);
        }

        $query = array_filter([
            'client_id' => $this->provider->clientId(),
            'post_logout_redirect_uri' => $this->provider->postLogoutRedirectUri(),
            'state' => Str::random(40),
            'id_token_hint' => is_string($idToken) ? $idToken : null,
        ]);

        $separator = str_contains($endSessionEndpoint, '?') ? '&' : '?';

        return redirect()->away($endSessionEndpoint.$separator.http_build_query($query, '', '&', PHP_QUERY_RFC3986));
    }

    /**
     * End local sessions for a back-channel logout.
     *
     * With a sid only the sessions of that Auth session end. Without a sid, or
     * when the sid is unknown here, all sessions of the user end.
     *
     * @return int number of ended sessions (best effort, for logging)
     */
    public function logout(?string $sub, ?string $sid): int
    {
        $user = $sub !== null ? User::where('auth_sub', $sub)->first() : null;

        if ($sid !== null) {
            $mapped = OidcSession::where('sid', $sid)
                ->when($user, fn ($query) => $query->where('user_id', $user->getKey()))
                ->get();

            if ($mapped->isNotEmpty() || $user === null) {
                return $this->destroyMapped($mapped);
            }
        }

        if ($user === null) {
            return 0;
        }

        $ended = $this->destroyMapped(OidcSession::where('user_id', $user->getKey())->get());

        if ($this->sessions->getDefaultDriver() === 'database') {
            $ended += DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->delete();
        }

        // Invalidate "remember me" cookies as well.
        $user->forceFill(['remember_token' => Str::random(60)])->saveQuietly();

        return $ended;
    }

    /**
     * @param  iterable<OidcSession>  $mapped
     */
    private function destroyMapped(iterable $mapped): int
    {
        $handler = $this->sessions->driver()->getHandler();
        $ended = 0;

        foreach ($mapped as $row) {
            $handler->destroy($row->session_id);
            $row->delete();
            $ended++;
        }

        return $ended;
    }
}
