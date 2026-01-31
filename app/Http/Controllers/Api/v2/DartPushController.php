<?php

namespace App\Http\Controllers\Api\v2;

use App\Models\DartPushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class DartPushController extends Controller
{
    /**
     * Get the VAPID public key for client-side subscription.
     */
    public function vapidPublicKey(): JsonResponse
    {
        return $this->sendResponse([
            'publicKey' => config('webpush.vapid.public_key'),
        ], 'VAPID public key retrieved.');
    }

    /**
     * Store or update push subscription.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
            'endpoint' => ['required', 'url'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'user_id' => ['nullable', 'integer'],
        ]);

        // Check if subscription already exists for this endpoint
        $existing = DartPushSubscription::findByEndpoint($validated['endpoint']);

        if ($existing) {
            // Update existing subscription
            $existing->update([
                'device_id' => $validated['device_id'],
                'user_id' => $validated['user_id'] ?? null,
                'p256dh_key' => $validated['keys']['p256dh'],
                'auth_key' => $validated['keys']['auth'],
                'user_agent' => $request->userAgent(),
            ]);
            $subscription = $existing;
        } else {
            // Create new subscription
            $subscription = DartPushSubscription::create([
                'device_id' => $validated['device_id'],
                'user_id' => $validated['user_id'] ?? null,
                'endpoint' => $validated['endpoint'],
                'p256dh_key' => $validated['keys']['p256dh'],
                'auth_key' => $validated['keys']['auth'],
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $this->sendResponse([
            'id' => $subscription->id,
        ], 'Push subscription registered successfully.');
    }

    /**
     * Remove push subscription.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url'],
        ]);

        $subscription = DartPushSubscription::findByEndpoint($validated['endpoint']);

        if ($subscription) {
            $subscription->delete();
        }

        return $this->sendResponse(null, 'Push subscription removed successfully.');
    }

    /**
     * Send push notification to all Dart app subscribers.
     * This should be called from a deployment script or admin panel.
     */
    public function sendUpdateNotification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'version' => ['nullable', 'string', 'max:50'],
            'url' => ['nullable', 'url'],
        ]);

        $auth = [
            'VAPID' => [
                'subject' => config('webpush.vapid.subject', 'mailto:admin@blubber-lounge.de'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ];

        $webPush = new WebPush($auth);

        $payload = json_encode([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'version' => $validated['version'] ?? null,
            'url' => $validated['url'] ?? '/',
            'icon' => '/pwa-192x192.png',
            'badge' => '/pwa-192x192.png',
        ]);

        $subscriptions = DartPushSubscription::all();
        $sent = 0;
        $failed = 0;

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'keys' => [
                    'p256dh' => $sub->p256dh_key,
                    'auth' => $sub->auth_key,
                ],
            ]);

            $webPush->queueNotification($subscription, $payload);
        }

        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $sent++;
            } else {
                $failed++;
                // Remove invalid subscriptions
                if ($report->isSubscriptionExpired()) {
                    DartPushSubscription::where('endpoint', $report->getEndpoint())->delete();
                }
            }
        }

        return $this->sendResponse([
            'sent' => $sent,
            'failed' => $failed,
            'total' => $subscriptions->count(),
        ], "Push notifications sent: {$sent} successful, {$failed} failed.");
    }
}
