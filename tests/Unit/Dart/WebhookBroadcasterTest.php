<?php

namespace Tests\Unit\Dart;

use Tests\TestCase;
use App\Services\Dart\WebhookBroadcaster;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\DartGame;

class WebhookBroadcasterTest extends TestCase
{
    private WebhookBroadcaster $broadcaster;

    protected function setUp(): void
    {
        parent::setUp();
        $this->broadcaster = app(WebhookBroadcaster::class);
    }

    public function test_broadcast_sends_to_configured_webhooks(): void
    {
        Http::fake();
        config(['dart.webhooks' => ['https://example.com/webhook']]);

        $game = DartGame::factory()->create();
        $this->broadcaster->broadcast($game, 'test.event', ['foo' => 'bar']);

        Http::assertSentCount(1);
    }

    public function test_broadcast_with_no_webhooks_sends_nothing(): void
    {
        Http::fake();
        config(['dart.webhooks' => []]);

        $game = DartGame::factory()->create();
        $this->broadcaster->broadcast($game, 'test.event', []);

        Http::assertSentCount(0);
    }

    public function test_broadcast_includes_required_payload_fields(): void
    {
        Http::fake();
        config(['dart.webhooks' => ['https://example.com/webhook']]);

        $game = DartGame::factory()->create();
        $this->broadcaster->broadcast($game, 'game.started', ['test' => 'data']);

        Http::assertSent(function ($request) use ($game) {
            $json = $request->body();
            return str_contains($json, '"event":"game.started"')
                && str_contains($json, '"game_id":' . $game->id)
                && str_contains($json, '"timestamp"');
        });
    }

    public function test_broadcast_with_multiple_webhooks(): void
    {
        Http::fake();
        config(['dart.webhooks' => [
            'https://webhook1.com/endpoint',
            'https://webhook2.com/endpoint',
        ]]);

        $game = DartGame::factory()->create();
        $this->broadcaster->broadcast($game, 'test.event', []);

        Http::assertSentCount(2);
    }

    public function test_broadcast_handles_failed_webhooks_gracefully(): void
    {
        Http::fake(['https://example.com/webhook' => Http::response(status: 500)]);
        Log::shouldReceive('warning')->once();
        config(['dart.webhooks' => ['https://example.com/webhook']]);

        $game = DartGame::factory()->create();
        $this->broadcaster->broadcast($game, 'test.event', []);
    }
}
