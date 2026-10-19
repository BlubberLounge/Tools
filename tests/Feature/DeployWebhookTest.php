<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route;
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

        config(['deploy.php_binary' => 'php-test-cli']);
        Process::fake(['*' => Process::result('done')]);

        $this->withToken('test-deploy-secret')
            ->postJson(self::URL)
            ->assertOk()
            ->assertJson(['success' => true]);

        Process::assertRan(fn ($process) => $process->command === ['php-test-cli', 'artisan', 'app:deploy', '--force', '--skip-build']);
    }

    public function test_webhook_route_skips_csrf(): void
    {
        // CSRF is never checked in tests, so look at the route itself: the
        // CI call has no session and failed with 419 when this was missing.
        $route = Route::getRoutes()->getByName('deploy.webhook');

        $this->assertContains(PreventRequestForgery::class, $route->excludedMiddleware());
    }

    public function test_without_a_configured_binary_the_webhook_picks_one(): void
    {
        config(['deploy.php_binary' => null]);
        Process::fake(['*' => Process::result('done')]);

        $this->withToken('test-deploy-secret')->postJson(self::URL)->assertOk();

        Process::assertRan(fn ($process) => is_string($process->command[0]) && $process->command[0] !== ''
            && $process->command[1] === 'artisan');
    }
}
