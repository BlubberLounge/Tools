<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        then: function () {
            // API versioning from RouteServiceProvider
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api_v1.php'));

            Route::middleware('api')
                ->prefix('api/v2')
                ->group(base_path('routes/api_v2.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Configure rate limiting
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Custom middleware appended to web group
        $middleware->web(append: [
            \App\Http\Middleware\DeviceActivity::class,
            \App\Http\Middleware\Localization::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'role' => \jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyRole::class,
            'permission' => \jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyPermission::class,
            'level' => \jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyLevel::class,
            'invitation.verifyToken' => \App\Http\Middleware\VerifyInvitationToken::class,
        ]);

        // Configure authentication redirects
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/home');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);
    })
    ->booting(function () {
        // Configure rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(500)->by($request->user()?->id ?: $request->ip());
        });
    })
    ->create();
