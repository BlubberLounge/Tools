<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class signedURL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:signedURL {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->newLine();
        $this->line('generated URL -> ');
        $this->info(URL::temporarySignedRoute('user.acquaintanceAdd', now()->addMinutes(config('custom.QRCode.expiration')), ['u' => $this->argument('user')]));
        $this->newLine();
    }
}
