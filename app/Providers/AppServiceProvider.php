<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        // Blade::directive('datetime', function (string $expression) {
        //     return "<?php echo ($expression)->format('m/d/Y H:i'); ? >";
        // });
        // use in blade like @datetime(now())

        // never sent a message to a real email when developing this application
        if ($this->app->environment('local'))
            Mail::alwaysTo(env('MAIL_TO_DEVELOPMENT', 'info@blubber-lounge.de'));

        if($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
