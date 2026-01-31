<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'de'; // Default locale

        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } elseif (Auth::guard('web')->check()) {
            $languageSetting = Auth::user()->settings->get('language');
            if ($languageSetting) {
                $locale = $languageSetting->value;
                Session::put('locale', $locale);
            }
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
