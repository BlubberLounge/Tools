<?php

namespace App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use chillerlan\QRCode\{QRCode, QROptions};

use App\Models\DartGame;
use App\Models\Feedback;
use App\Models\Invitation;
use App\Models\User;
use App\Policies\DartGamePolicy;
use App\Policies\FeedbackPolicy;
use App\Policies\InvitationPolicy;
use App\Policies\UserPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(QRCode::class, function (Application $app) {
            $options = new QROptions([
                'version' => 7, // 7 not 5 because of bit length, may use a url shortener at a later point
            ]);

            return new QRCode($options);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * Event listeners in app/Listeners are registered by event discovery and the
     * pagination uses Laravel's Tailwind views, so neither is configured here.
     */
    public function boot(): void
    {
        // Register policies (migrated from AuthServiceProvider)
        Gate::policy(DartGame::class, DartGamePolicy::class);
        Gate::policy(Feedback::class, FeedbackPolicy::class);
        Gate::policy(Invitation::class, InvitationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        // Never send a message to a real email when developing this application
        if ($this->app->environment('local')) {
            Mail::alwaysTo(env('MAIL_TO_DEVELOPMENT', 'info@blubber-lounge.de'));
        }

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
