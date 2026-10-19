<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\BlubberLoungeAuth\OidcSessionManager;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        $login = request()->input('name');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        request()->merge([$field => $login]);
        return $field;
    }

    /**
     * Log the user out. Sessions started with "Sign in with BlubberLounge"
     * are ended at BlubberLounge Auth as well.
     */
    public function logout(Request $request)
    {
        return app(OidcSessionManager::class)->logoutResponse($request, '/');
    }
}
