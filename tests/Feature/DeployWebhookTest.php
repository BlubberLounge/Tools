<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DeployWebhookTest extends TestCase
{
    private const URL = '/api/deploy/webhook';

    protected function setUp(): void
    {
        parent::setUp();
        config(['deploy.webhook_secret' => 'test-deploy-secret']);
    }

    public function test_request_without_token_is_rejected(): void
    {
        $this->postJson(self::URL)->assertUnauthorized();
    }

    public function test_request_with_wrong_token_is_rejected(): void
    {
        $this->withToken('wrong')->postJson(self::URL)->assertUnauthorized();
    }

    public function test_webhook_is_closed_without_a_configured_secret(): void
    {
        config(['deploy.webhook_secret' => null]);

        $this->withToken('')->postJson(self::URL)->assertUnauthorized();
    }

    public function test_valid_token_runs_the_deploy_command(): void
    {
        $this->assertFileDoesNotExist(base_path('_deploy.zip'));

        Artisan::shouldReceive('call')
            ->once()
            ->with('app:deploy', ['--force' => true, '--skip-build' => true])
            ->andReturn(0);
        Artisan::shouldReceive('output')->andReturn('done');

        $this->withToken('test-deploy-secret')
            ->postJson(self::URL)
            ->assertOk()
            ->assertJson(['success' => true]);
    }
}
