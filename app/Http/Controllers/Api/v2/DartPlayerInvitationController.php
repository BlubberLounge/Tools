<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Models\Acquaintance;
use App\Models\DartPlayerInvitation;
use App\Models\User;
use App\Services\Dart\LocalPlayerResolver;
use App\Services\OAuth\BlubberLoungeOAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DartPlayerInvitationController extends Controller
{
    /**
     * Invite an offline player to register a BlubberLounge account.
     *
     * POST /api/v2/dart/invitations
     */
    public function store(Request $request, BlubberLoungeOAuthService $oauthService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'local_player_id' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $validated = $validator->validated();
        $validated['email'] = strtolower($validated['email']);
        $user = $request->user();

        // Check if already a registered Tools user
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser) {
            return $this->sendError(
                'A user with this email already exists.',
                ['user_id' => $existingUser->id, 'name' => $existingUser->name],
                409
            );
        }

        // Check for existing pending invitation
        $existingInvitation = DartPlayerInvitation::where('email', $validated['email'])
            ->whereIn('status', ['pending', 'sent'])
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return $this->sendError(
                'An active invitation already exists for this email.',
                [
                    'invitation_id' => $existingInvitation->id,
                    'status' => $existingInvitation->status,
                    'expires_at' => $existingInvitation->expires_at->toIso8601String(),
                ],
                409
            );
        }

        // Call Auth server to create the invitation
        try {
            $authResponse = $oauthService->createInvitation([
                'email' => $validated['email'],
                'firstname' => $validated['firstname'] ?? null,
                'lastname' => $validated['lastname'] ?? null,
                'user_external_id' => $user->external_id,
                'client_id' => config('services.blubberlounge.client_id'),
                'metadata' => [
                    'local_player_id' => $validated['local_player_id'] ?? null,
                    'invited_by_tools_user_id' => $user->id,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->sendError(
                'Failed to create invitation on Auth server.',
                ['detail' => $e->getMessage()],
                502
            );
        }

        // If already registered on Auth, resolve locally and begin migration
        if (!empty($authResponse['already_registered']) && isset($authResponse['user'])) {
            return $this->handleAlreadyRegistered(
                $authResponse['user'],
                $validated,
                $user,
            );
        }

        if (!isset($authResponse['success']) || !$authResponse['success']) {
            $error = $authResponse['error'] ?? 'unknown_error';
            $message = $authResponse['message'] ?? 'Auth server rejected the invitation.';

            // If Auth already has a pending invitation for this email
            if ($error === 'invitation_exists' && isset($authResponse['invitation'])) {
                return $this->sendError(
                    $message,
                    [
                        'auth_invitation_token' => $authResponse['invitation']['token'],
                        'status' => $authResponse['invitation']['status'],
                        'expires_at' => $authResponse['invitation']['expires_at'],
                    ],
                    409
                );
            }

            return $this->sendError($message, $authResponse, 422);
        }

        // Store local tracking record (email already lowercased above)
        $invitation = DartPlayerInvitation::create([
            'auth_invitation_token' => $authResponse['invitation']['token'],
            'invited_by_user_id' => $user->id,
            'email' => $validated['email'],  // already lowercased
            'firstname' => $validated['firstname'] ?? null,
            'lastname' => $validated['lastname'] ?? null,
            'local_player_id' => $validated['local_player_id'] ?? null,
            'status' => 'sent',
            'expires_at' => $authResponse['invitation']['expires_at'],
        ]);

        return $this->sendResponse([
            'invitation_id' => $invitation->id,
            'auth_invitation_token' => $invitation->auth_invitation_token,
            'email' => $invitation->email,
            'status' => $invitation->status,
            'expires_at' => $invitation->expires_at->toIso8601String(),
            'local_player_id' => $invitation->local_player_id,
            'registration_url' => $authResponse['invitation']['registration_url'] ?? null,
        ], 'Invitation sent successfully.');
    }

    /**
     * List all invitations created by the authenticated user.
     *
     * GET /api/v2/dart/invitations
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $invitations = DartPlayerInvitation::where('invited_by_user_id', $user->id)
            ->with('registeredUser:id,name,firstname,lastname')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($inv) => [
                'id' => $inv->id,
                'email' => $inv->email,
                'firstname' => $inv->firstname,
                'lastname' => $inv->lastname,
                'local_player_id' => $inv->local_player_id,
                'status' => $inv->status,
                'registered_user_id' => $inv->registered_user_id,
                'registered_user' => $inv->registeredUser ? [
                    'id' => $inv->registeredUser->id,
                    'name' => $inv->registeredUser->name,
                ] : null,
                'expires_at' => $inv->expires_at?->toIso8601String(),
                'created_at' => $inv->created_at->toIso8601String(),
            ]);

        return $this->sendResponse($invitations, 'Player invitations retrieved.');
    }

    /**
     * Check the status of a specific invitation.
     * Syncs with Auth server if the local status is still pending/sent.
     *
     * GET /api/v2/dart/invitations/{invitation}/status
     */
    public function status(DartPlayerInvitation $invitation, Request $request, BlubberLoungeOAuthService $oauthService): JsonResponse
    {
        $user = $request->user();

        if ($invitation->invited_by_user_id !== $user->id) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        // Sync status from Auth if still pending locally
        if ($invitation->isPending() && $invitation->auth_invitation_token) {
            try {
                $authStatus = $oauthService->getInvitationStatus($invitation->auth_invitation_token);

                if (isset($authStatus['status']) && $authStatus['status'] === 'registered') {
                    // User registered on Auth — try to find matching Tools user
                    $externalId = $authStatus['registered_user']['external_id'] ?? null;
                    $toolsUser = $externalId ? User::where('external_id', $externalId)->first() : null;

                    // Fallback: try finding by invitation email
                    if (!$toolsUser && $invitation->email) {
                        $toolsUser = User::where('email', $invitation->email)->first();
                    }

                    if ($toolsUser) {
                        $invitation->markAsRegistered($toolsUser);
                    }
                    // Don't update status without linking user — let token exchange handle it
                } elseif (isset($authStatus['status']) && $authStatus['status'] === 'expired') {
                    $invitation->update(['status' => 'expired']);
                }
            } catch (\Exception $e) {
                // Auth sync failed — continue with local data
            }
        }

        $invitation->refresh();
        $invitation->load('registeredUser:id,name,firstname,lastname');

        return $this->sendResponse([
            'id' => $invitation->id,
            'email' => $invitation->email,
            'local_player_id' => $invitation->local_player_id,
            'status' => $invitation->status,
            'registered_user_id' => $invitation->registered_user_id,
            'registered_user' => $invitation->registeredUser ? [
                'id' => $invitation->registeredUser->id,
                'name' => $invitation->registeredUser->name,
            ] : null,
            'invited_by' => [
                'id' => $invitation->invitedBy->id,
                'name' => $invitation->invitedBy->name,
            ],
            'expires_at' => $invitation->expires_at?->toIso8601String(),
        ], 'Invitation status retrieved.');
    }

    /**
     * Delete an invitation.
     *
     * DELETE /api/v2/dart/invitations/{invitation}
     */
    public function destroy(DartPlayerInvitation $invitation, Request $request): JsonResponse
    {
        $user = $request->user();

        if ($invitation->invited_by_user_id !== $user->id) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        $invitation->delete();

        return $this->sendResponse(null, 'Invitation deleted.');
    }

    /**
     * Handle the case where the invited email already belongs to a registered Auth user.
     * Find or defer the Tools user mapping and trigger backfill if possible.
     */
    private function handleAlreadyRegistered(array $authUser, array $validated, User $inviter): JsonResponse
    {
        $externalId = $authUser['external_id'] ?? null;
        $localPlayerId = $validated['local_player_id'] ?? null;

        $toolsUser = $externalId ? User::where('external_id', $externalId)->first() : null;

        // Check for existing invitation for this email (avoid duplicates)
        $existingInvitation = DartPlayerInvitation::where('email', $validated['email'])
            ->where('invited_by_user_id', $inviter->id)
            ->first();

        if ($toolsUser) {
            // Tools user exists — immediate migration
            $invitation = $existingInvitation ?? DartPlayerInvitation::create([
                'invited_by_user_id' => $inviter->id,
                'email' => $validated['email'],
                'firstname' => $validated['firstname'] ?? null,
                'lastname' => $validated['lastname'] ?? null,
                'local_player_id' => $localPlayerId,
                'status' => 'sent',
            ]);

            if ($existingInvitation && $localPlayerId && !$existingInvitation->local_player_id) {
                $invitation->update(['local_player_id' => $localPlayerId]);
            }

            // markAsRegistered handles backfill + acquaintance creation
            $invitation->markAsRegistered($toolsUser);

            return $this->sendResponse([
                'already_registered' => true,
                'invitation_id' => $invitation->id,
                'user' => [
                    'id' => $toolsUser->id,
                    'external_id' => $toolsUser->external_id,
                    'name' => $toolsUser->name,
                    'firstname' => $toolsUser->firstname,
                    'lastname' => $toolsUser->lastname,
                    'img' => $toolsUser->img,
                ],
                'local_player_id' => $localPlayerId,
            ], 'User already registered. Migration completed.');
        }

        // Tools user not found yet — store invitation for deferred migration
        if (!$existingInvitation) {
            DartPlayerInvitation::create([
                'invited_by_user_id' => $inviter->id,
                'email' => $validated['email'],
                'firstname' => $validated['firstname'] ?? null,
                'lastname' => $validated['lastname'] ?? null,
                'local_player_id' => $localPlayerId,
                'status' => 'sent',
            ]);
        }

        return $this->sendResponse([
            'already_registered' => true,
            'pending_migration' => true,
            'auth_user' => $authUser,
            'local_player_id' => $localPlayerId,
        ], 'User already registered on Auth. Migration will complete on next login.');
    }

    /**
     * Link a local player to an existing user via QR token scan.
     * The device owner scans the guest's acquaintance QR code.
     *
     * POST /api/v2/dart/players/local/link
     */
    public function linkByQrToken(Request $request): JsonResponse
    {
        $request->validate([
            'qr_token' => ['required', 'string'],
            'local_player_id' => ['required', 'string', 'max:255'],
        ]);

        // Validate QR token (same HMAC logic as AcquaintanceController)
        $parts = explode('.', $request->input('qr_token'));
        if (count($parts) !== 2) {
            return $this->sendError('Invalid token format.', [], 422);
        }

        [$payload, $signature] = $parts;

        $expectedSignature = hash_hmac('sha256', $payload, config('app.key'));
        if (!hash_equals($expectedSignature, $signature)) {
            return $this->sendError('Invalid token signature.', [], 422);
        }

        $data = json_decode(base64_decode($payload), true);
        if (!$data || !isset($data['eid'], $data['exp'])) {
            return $this->sendError('Invalid token payload.', [], 422);
        }

        if (now()->timestamp > $data['exp']) {
            return $this->sendError('QR code has expired.', [], 422);
        }

        $inviter = $request->user();
        $targetExternalId = $data['eid'];
        $localPlayerId = $request->input('local_player_id');

        $targetUser = User::where('external_id', $targetExternalId)->first();
        if (!$targetUser) {
            return $this->sendError('User not found.', [], 404);
        }

        if ($targetUser->id === $inviter->id) {
            return $this->sendError('You cannot link a local player to yourself.', [], 422);
        }

        // Create invitation record with immediate registered status
        $invitation = DartPlayerInvitation::create([
            'invited_by_user_id' => $inviter->id,
            'email' => $targetUser->email,
            'firstname' => $targetUser->firstname,
            'lastname' => $targetUser->lastname,
            'local_player_id' => $localPlayerId,
            'status' => 'sent',
        ]);

        $invitation->markAsRegistered($targetUser);

        // Backfill game data
        $backfillResult = app(LocalPlayerResolver::class)->backfill($localPlayerId, $targetUser->id);

        return $this->sendResponse([
            'invitation_id' => $invitation->id,
            'user' => [
                'id' => $targetUser->id,
                'external_id' => $targetUser->external_id,
                'name' => $targetUser->name,
                'firstname' => $targetUser->firstname,
                'lastname' => $targetUser->lastname,
                'img' => $targetUser->img,
            ],
            'local_player_id' => $localPlayerId,
            'backfill' => $backfillResult,
        ], 'Local player linked successfully.');
    }
}
