<?php

namespace App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use chillerlan\QRCode\{QRCode, QROptions};

use App\Models\DartGame;
use App\Models\Feedback;
use App\Models\Invitation;
use App\Models\User;
use App\Policies\DartGamePolicy;
use App\Policies\FeedbackPolicy;
use App\Policies\InvitationPolicy;
use App\Policies\UserPolicy;
use App\Listeners\RegisteredListener;
use App\Listeners\LoginListener;
use App\Listeners\LogSendingMessage;
use App\Listeners\LogSentMessage;

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
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Register policies (migrated from AuthServiceProvider)
        Gate::policy(DartGame::class, DartGamePolicy::class);
        Gate::policy(Feedback::class, FeedbackPolicy::class);
        Gate::policy(Invitation::class, InvitationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        // Register event listeners (migrated from EventServiceProvider)
        Event::listen(Registered::class, SendEmailVerificationNotification::class);
        Event::listen(Registered::class, RegisteredListener::class);
        Event::listen(Login::class, LoginListener::class);
        Event::listen(MessageSending::class, LogSendingMessage::class);
        Event::listen(MessageSent::class, LogSentMessage::class);

        // Never send a message to a real email when developing this application
        if ($this->app->environment('local')) {
            Mail::alwaysTo(env('MAIL_TO_DEVELOPMENT', 'info@blubber-lounge.de'));
        }

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
