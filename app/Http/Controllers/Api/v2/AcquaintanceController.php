<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Models\Acquaintance;
use App\Models\User;
use App\Enums\AcquaintanceStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcquaintanceController extends Controller
{
    /**
     * List all accepted acquaintances for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $acquaintances = $user->acceptedAcquaintancesMerged()->map(fn ($acq) => [
            'id'          => $acq->id,
            'external_id' => $acq->external_id,
            'name'        => $acq->name,
            'firstname'   => $acq->firstname,
            'lastname'    => $acq->lastname,
            'img'         => $acq->img,
        ]);

        return $this->sendResponse($acquaintances, 'Acquaintances retrieved.');
    }

    /**
     * Generate a signed, time-limited QR token for the authenticated user.
     */
    public function qrToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $expiresAt = now()->addMinutes(5);

        $payload = base64_encode(json_encode([
            'eid' => $user->external_id,
            'exp' => $expiresAt->timestamp,
        ]));

        $signature = hash_hmac('sha256', $payload, config('app.key'));
        $token = "{$payload}.{$signature}";

        return $this->sendResponse([
            'token'      => $token,
            'expires_at' => $expiresAt->toIso8601String(),
        ], 'QR token generated.');
    }

    /**
     * Create an acquaintance by scanning a signed QR token.
     * Validates HMAC signature and expiry before creating the relationship.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        // Split token into payload and signature
        $parts = explode('.', $request->input('token'));
        if (count($parts) !== 2) {
            return $this->sendError('Invalid token format.', [], 422);
        }

        [$payload, $signature] = $parts;

        // Verify HMAC signature
        $expectedSignature = hash_hmac('sha256', $payload, config('app.key'));
        if (!hash_equals($expectedSignature, $signature)) {
            return $this->sendError('Invalid token signature.', [], 422);
        }

        // Decode and validate payload
        $data = json_decode(base64_decode($payload), true);
        if (!$data || !isset($data['eid'], $data['exp'])) {
            return $this->sendError('Invalid token payload.', [], 422);
        }

        // Check expiry
        if (now()->timestamp > $data['exp']) {
            return $this->sendError('QR code has expired.', [], 422);
        }

        $scanner = $request->user();
        $targetExternalId = $data['eid'];

        $target = User::where('external_id', $targetExternalId)->first();
        if (!$target) {
            return $this->sendError('User not found.', [], 404);
        }

        if ($target->id === $scanner->id) {
            return $this->sendError('You cannot add yourself as an acquaintance.', [], 422);
        }

        // Check both directions
        $existing = Acquaintance::where(function ($q) use ($scanner, $target) {
            $q->where('transmitter_user_id', $scanner->id)
              ->where('receiver_user_id', $target->id);
        })->orWhere(function ($q) use ($scanner, $target) {
            $q->where('transmitter_user_id', $target->id)
              ->where('receiver_user_id', $scanner->id);
        })->first();

        if ($existing) {
            if ($existing->status === AcquaintanceStatus::DENIED->value) {
                $existing->update(['status' => AcquaintanceStatus::ACCEPTED->value]);
                return $this->sendResponse($this->formatResponse($existing, $scanner), 'Acquaintance re-accepted.');
            }
            return $this->sendError('Acquaintance already exists.', ['status' => $existing->status], 409);
        }

        $acquaintance = Acquaintance::create([
            'transmitter_user_id' => $scanner->id,
            'receiver_user_id'    => $target->id,
            'status'              => AcquaintanceStatus::ACCEPTED->value,
            'showOnHomeView'      => true,
        ]);

        return $this->sendResponse($this->formatResponse($acquaintance, $scanner), 'Acquaintance created.');
    }

    private function formatResponse(Acquaintance $acq, User $currentUser): array
    {
        $otherId = $acq->transmitter_user_id === $currentUser->id
            ? $acq->receiver_user_id
            : $acq->transmitter_user_id;
        $other = User::find($otherId);

        return [
            'acquaintance_id' => $acq->id,
            'user' => $other ? [
                'id'          => $other->id,
                'external_id' => $other->external_id,
                'name'        => $other->name,
                'firstname'   => $other->firstname,
                'lastname'    => $other->lastname,
                'img'         => $other->img,
            ] : null,
            'status' => $acq->status,
        ];
    }
}
