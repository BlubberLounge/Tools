<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OAuth\BlubberLoungeOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class BlubberLoungeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function redirect(BlubberLoungeOAuthService $service)
    {
        return redirect()->away($service->getAuthorizationUrl());
    }

    public function callback(Request $request, BlubberLoungeOAuthService $service)
    {
        $stored = session()->pull('oauth_state');

        if ($request->get('state') !== $stored) {
            abort(403, 'Invalid OAuth state');
        }

        $externalUser = $service->user($request);
        $externalId = $externalUser['id'];
        $email      = $externalUser['email'] ?? null;

        $user = User::where('external_id', $externalId)->first();

        if (!$user && $email) {
            $user = User::where('email', $email)->first();

            if ($user)
                $user->update([
                    'external_id' => $externalId,
                ]);
        }

        if (!$user) {
            $user = User::create([
                'name'              => $externalUser['name'],
                'firstname'         => $externalUser['firstname'] ?? $externalUser['name'],
                'lastname'          => $externalUser['lastname'] ?? null,
                'dob'               => null,
                'img'               => '/storage/avatar/avatar-dummy.jpg',
                'external_id'       => $externalId,
                'password'          => 'external_account', // external accounts dont have a password, so its impossible login directly without OAuth authorisation //Hash::make('123'),
            ]);
        }

        $user->update([
            'email'             => $email,
            'email_verified_at' => $externalUser['email_verified_at'] ?? now(), // null,
            'access_token'      => $externalUser['access_token'],
            'refresh_token'     => $externalUser['refresh_token'],
            'token_expires_at'  => now()->addSeconds($externalUser['expires_in'] ?? 3600),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('index'));
    }
}
