<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateDeployToken extends Command
{
    protected $signature = 'deploy:generate-token
        {--length=64 : Token length}
        {--write : Write the token to .env automatically}';

    protected $description = 'Generate a secure DEPLOY_WEBHOOK_SECRET token';

    public function handle(): int
    {
        $token = Str::random((int) $this->option('length'));

        $this->components->info('Generated deploy webhook token:');
        $this->newLine();
        $this->line("  <comment>{$token}</comment>");
        $this->newLine();

        if ($this->option('write')) {
            $this->writeToEnv($token);
        } else {
            $this->components->bulletList([
                'Add this to your server .env as DEPLOY_WEBHOOK_SECRET=<token>',
                'Add it as a GitHub repo secret named DEPLOY_WEBHOOK_SECRET',
                'Or re-run with --write to update .env automatically',
            ]);
        }

        return self::SUCCESS;
    }

    private function writeToEnv(string $token): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->components->error('.env file not found.');
            return;
        }

        $contents = file_get_contents($envPath);

        if (preg_match('/^DEPLOY_WEBHOOK_SECRET=.*/m', $contents)) {
            $contents = preg_replace(
                '/^DEPLOY_WEBHOOK_SECRET=.*/m',
                "DEPLOY_WEBHOOK_SECRET={$token}",
                $contents,
            );
        } else {
            $contents .= "\nDEPLOY_WEBHOOK_SECRET={$token}\n";
        }

        file_put_contents($envPath, $contents);

        $this->components->info('Written to .env');
    }
}
