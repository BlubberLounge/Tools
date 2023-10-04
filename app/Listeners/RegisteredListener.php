<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

use App\Classes\DeviceTracker;
use App\Models\User;
use App\Notifications\UserRegisteredNotification;


class RegisteredListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        DeviceTracker::detectRegistration();

        foreach(User::aboveLevel5()->get() as $user) {
            $user->notify(new UserRegisteredNotification($user, $event->user));
        }
    }
}
