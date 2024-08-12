<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\DartGame;
use App\Models\Feedback;
use App\Models\Invitation;
use App\Models\User;
use App\Policies\DartGamePolicy;
use App\Policies\FeedbackPolicy;
use App\Policies\InvitationPolicy;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DartGame::class => DartGamePolicy::class,
        Feedback::class => FeedbackPolicy::class,
        Invitation::class => InvitationPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // VerifyEmail::toMailUsing(function ($notifiable, $url) {
        //     return (new MailMessage)
        //         ->subject('BlubberLounge '. env('APP_NAME').' - Verify your Email Address')
        //         ->line(__('Please click the button below to verify your email address.'))
        //         ->action('Verify Email Address', $url)
        //         ->line(__('If you did not create an account, no further action is required.'));
        // });
    }
}
