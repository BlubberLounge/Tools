<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use App\Classes\DeviceTracker;
use App\Models\User;

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
        if (Auth::guard('web')->check()) {
            DeviceTracker::detectRegistration();
        }
        // $abc = User::admins()->first();
        // dd($abc);
    }
}
