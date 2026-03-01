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
        // Accept token from Authorization header (preferred) or request body (fallback)
        $accessToken = $request->bearerToken();
        if (!$accessToken) {
            $validated = $request->validate([
                'access_token' => ['required', 'string'],
            ]);
            $accessToken = $validated['access_token'];
        }

        $externalUser = $oauthService->validateExternalToken($accessToken);

        if (!$externalUser || !isset($externalUser['id'])) {
            return response()->json(['error' => 'Invalid or expired OAuth token'], 401);
        }

        $externalId = $externalUser['id'];
        $email = $externalUser['email'] ?? null;
        $emailVerified = $externalUser['email_verified_at'] ?? null;

        // Find by external_id
        $user = User::where('external_id', $externalId)->first();

        // If not found, try by email — but only if the Auth server confirmed the email is verified
        if (!$user && $email) {
            if ($emailVerified) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $user->update(['external_id' => $externalId]);
                }
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

        // Update email if changed (only if verified by Auth server)
        $updateData = [];
        if ($emailVerified) {
            $updateData['email_verified_at'] = $emailVerified;
        }

        if ($email && $emailVerified && $user->email !== $email) {
            $emailTaken = User::where('email', $email)->where('id', '!=', $user->id)->exists();
            if (!$emailTaken) {
                $updateData['email'] = $email;
            }
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // Issue Sanctum token
        $sanctumToken = $user->createToken('dart-pwa')->plainTextToken;

        // Collect local_player_id → registered user mappings
        $localPlayerMappings = [];
        if ($email) {
            $invitations = DartPlayerInvitation::where('email', $email)
                ->where('status', 'registered')
                ->whereNotNull('local_player_id')
                ->whereNotNull('registered_user_id')
                ->with('registeredUser:id,external_id,name,firstname,lastname,img')
                ->get();

            $localPlayerMappings = $invitations->map(fn ($inv) => [
                'local_player_id' => $inv->local_player_id,
                'user_id' => $inv->registered_user_id,
                'external_id' => $inv->registeredUser?->external_id,
                'name' => $inv->registeredUser?->name,
                'firstname' => $inv->registeredUser?->firstname,
                'lastname' => $inv->registeredUser?->lastname,
                'img' => $inv->registeredUser?->img,
            ])->toArray();
        }

        return response()->json([
            'sanctum_token' => $sanctumToken,
            'user' => [
                'id'          => $user->id,
                'external_id' => $user->external_id,
                'name'        => $user->name,
                'firstname'   => $user->firstname,
                'lastname'    => $user->lastname,
                'email'       => $user->email,
                'img'         => $user->img,
            ],
            'is_new_user' => $isNewUser,
            'local_player_mappings' => $localPlayerMappings,
        ]);
    }
}
