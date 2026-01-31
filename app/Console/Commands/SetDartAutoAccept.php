<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetDartAutoAccept extends Command
{
    protected $signature = 'dart:set-auto-accept {--value=true : Set to true or false}';

    protected $description = 'Set dartGameInvitationAutoAccept for all users';

    public function handle(): int
    {
        $value = $this->option('value') === 'true';

        $users = User::all();
        $count = 0;

        $this->withProgressBar($users, function (User $user) use ($value, &$count) {
            $raw = $user->getRawOriginal('settings');
            $settings = is_string($raw) ? json_decode($raw, true) ?? [] : ($raw ?? []);

            $settings['dartGameInvitationAutoAccept'] = $value;

            $user->setRawAttributes(
                array_merge($user->getAttributes(), ['settings' => json_encode($settings)])
            );
            $user->save();
            $count++;
        });

        $this->newLine();
        $this->info("Updated {$count} users with dartGameInvitationAutoAccept = " . ($value ? 'true' : 'false'));

        return Command::SUCCESS;
    }
}
