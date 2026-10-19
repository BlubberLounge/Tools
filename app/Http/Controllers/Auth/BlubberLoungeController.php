<?php

namespace App\Http\Controllers\Auth;

use App\Events\BlubberLounge\SsoLoggedIn;
use App\Http\Controllers\Controller;
use App\Services\BlubberLoungeAuth\Exceptions\OAuthLoginException;
use App\Services\BlubberLoungeAuth\OAuthService;
use App\Services\BlubberLoungeAuth\OidcSessionManager;
use App\Services\BlubberLoungeAuth\OidcUserResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * "Sign in with BlubberLounge": OAuth 2.0 + OpenID Connect login against
 * BlubberLounge Auth (see App\Services\BlubberLoungeAuth).
 */
class BlubberLoungeController extends Controller
{
    public function __construct(
        private readonly OAuthService $oauth,
        private readonly OidcUserResolver $users,
        private readonly OidcSessionManager $oidcSessions,
    ) {}

    /**
     * Send the user to BlubberLounge Auth.
     */
    public function redirect(Request $request): RedirectResponse
    {
        return $this->oauth->redirect($request);
    }

    /**
     * Handle the redirect back from BlubberLounge Auth.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $identity = $this->oauth->handleCallback($request);
            $user = $this->users->resolve($identity);
        } catch (OAuthLoginException $e) {
            Log::info('Single sign-on login failed.', [
                'reason' => $e->reason,
                'error' => $e->getPrevious()?->getMessage(),
            ]);

            return $this->failed($e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return $this->failed('Sign-in with BlubberLounge failed. Please try again.');
        }

        Auth::login($user);
        $request->session()->regenerate();
        $this->oidcSessions->register($request, $user, $identity);

        SsoLoggedIn::dispatch($user, $identity);

        return redirect()->intended(route('home'));
    }

    private function failed(string $message): RedirectResponse
    {
        return redirect()->route('login')->with('status', $message);
    }
}
