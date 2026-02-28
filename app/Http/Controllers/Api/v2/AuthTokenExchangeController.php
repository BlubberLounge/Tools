<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Models\User;
use App\Models\DartPlayerInvitation;
use App\Services\OAuth\BlubberLoungeOAuthService;
use Illuminate\Http\Request;

class AuthTokenExchangeController extends Controller
{
    public function exchange(Request $request, BlubberLoungeOAuthService $oauthService)
    {
        $validated = $request->validate([
            'access_token' => ['required', 'string'],
        ]);

        $externalUser = $oauthService->validateExternalToken($validated['access_token']);

        if (!$externalUser || !isset($externalUser['id'])) {
            return response()->json(['error' => 'Invalid or expired OAuth token'], 401);
        }

        $externalId = $externalUser['id'];
        $email = $externalUser['email'] ?? null;

        // Find by external_id
        $user = User::where('external_id', $externalId)->first();

        // If not found, try by email and link
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['external_id' => $externalId]);
            }
        }

        // If still not found, create
        $isNewUser = false;
        if (!$user) {
            $isNewUser = true;
            $user = User::create([
                'name'        => $externalUser['name'],
                'firstname'   => $externalUser['firstname'] ?? $externalUser['name'],
                'lastname'    => $externalUser['lastname'] ?? null,
                'email'       => $email,
                'dob'         => null,
                'img'         => '/storage/avatar/avatar-dummy.jpg',
                'external_id' => $externalId,
                'password'    => 'external_account',
            ]);
        }

        // Auto-link pending player invitations by email
        if ($email) {
            $pendingInvitation = DartPlayerInvitation::where('email', $email)
                ->whereIn('status', ['pending', 'sent'])
                ->first();

            if ($pendingInvitation) {
                $pendingInvitation->markAsRegistered($user);
            }
        }

        // Update email if changed
        $updateData = [
            'email_verified_at' => $externalUser['email_verified_at'] ?? now(),
        ];

        if ($email && $user->email !== $email) {
            $emailTaken = User::where('email', $email)->where('id', '!=', $user->id)->exists();
            if (!$emailTaken) {
                $updateData['email'] = $email;
            }
        }

        $user->update($updateData);

        // Issue Sanctum token
        $sanctumToken = $user->createToken('dart-pwa')->plainTextToken;

        return response()->json([
            'sanctum_token' => $sanctumToken,
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'firstname' => $user->firstname,
                'lastname'  => $user->lastname,
                'email'     => $user->email,
                'img'       => $user->img,
            ],
            'is_new_user' => $isNewUser,
        ]);
    }
}
