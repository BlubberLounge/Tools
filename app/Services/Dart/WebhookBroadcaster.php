<?php

namespace App\Services\Dart;

use App\Models\DartGame;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookBroadcaster
{
    /**
     * Broadcastet ein Event an konfigurierbare Webhooks.
     * Erwartet: $game->webhooks (json-array) oder config('dart.webhooks')
     */
    public function broadcast(DartGame $game, string $event, array $payload): void
    {
        $webhooks = $this->getWebhooksForGame($game);
        $data = [
            'event' => $event,
            'game_id' => $game->id,
            'payload' => $payload,
            'timestamp' => now()->toIso8601String(),
        ];

        foreach ($webhooks as $url) {
            try {
                // Fire-and-forget: Zeitüberschreitung und Fehler logged
                $response = Http::timeout(3)->post($url, $data);
                if ($response->serverError() || $response->clientError()) {
                    Log::warning("Webhook failed for {$url}: {$response->status()}");
                }
            } catch (\Throwable $e) {
                Log::warning("Webhook exception for {$url}: ".$e->getMessage());
            }
        }
    }

    protected function getWebhooksForGame(DartGame $game): array
    {
        // Option A: Spiel-spezifische Webhooks in DB-Feld `webhooks` gespeichert
        if (property_exists($game, 'webhooks') && $game->webhooks) {
            return is_array($game->webhooks) ? $game->webhooks : json_decode($game->webhooks, true) ?? [];
        }

        // Fallback: globale config
        return config('dart.webhooks', []);
    }
}
