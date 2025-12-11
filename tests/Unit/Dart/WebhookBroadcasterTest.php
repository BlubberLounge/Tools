<?php

namespace Tests\Unit\Dart;

use Tests\TestCase;
use App\Models\DartGame;
use App\Services\Dart\WebhookBroadcaster;
use Illuminate\Support\Facades\Http;

class WebhookBroadcasterTest extends TestCase
{
    public function test_webhook_is_called(): void
    {
        Http::fake();

        config(['dart.webhooks' => ['https://example.org/webhook']]);

        $game = DartGame::factory()->create();

        app(WebhookBroadcaster::class)->broadcast($game, 'test.event', [
            'foo' => 'bar',
        ]);

        Http::assertSentCount(1);
    }
}
