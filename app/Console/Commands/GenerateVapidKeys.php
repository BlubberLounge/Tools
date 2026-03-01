<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'vapid:generate
                            {--show : Display the keys without modifying the .env file}';

    protected $description = 'Generate VAPID keys for Web Push notifications';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        if ($this->option('show')) {
            $this->info('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
            $this->info('VAPID_PRIVATE_KEY=' . $keys['privateKey']);

            return self::SUCCESS;
        }

        $envPath = $this->laravel->environmentFilePath();
        $env = file_get_contents($envPath);

        $updated = false;

        foreach ([
            'VAPID_PUBLIC_KEY' => $keys['publicKey'],
            'VAPID_PRIVATE_KEY' => $keys['privateKey'],
        ] as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
                $updated = true;
            } else {
                $env = rtrim($env) . "\n\n{$key}={$value}\n";
                $updated = true;
            }
        }

        if ($updated) {
            file_put_contents($envPath, $env);
        }

        $this->components->info('VAPID keys set successfully.');
        $this->components->twoColumnDetail('Public Key', $keys['publicKey']);
        $this->components->twoColumnDetail('Private Key', $keys['privateKey']);

        return self::SUCCESS;
    }
}
