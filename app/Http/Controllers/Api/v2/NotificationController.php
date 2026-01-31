<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Store or update push subscription for the authenticated user.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $request->user()->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth']
        );

        return $this->sendResponse(null, 'Push subscription registered successfully.');
    }

    /**
     * Remove push subscription for the authenticated user.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url'],
        ]);

        $request->user()->deletePushSubscription($validated['endpoint']);

        return $this->sendResponse(null, 'Push subscription removed successfully.');
    }

    /**
     * Get the VAPID public key for client-side subscription.
     */
    public function vapidPublicKey(): JsonResponse
    {
        return $this->sendResponse([
            'publicKey' => config('webpush.vapid.public_key'),
        ], 'VAPID public key retrieved.');
    }
}
