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
        $locale = null;

        if(Session::has('locale')) {
            $locale = Session::get('locale');
        } else if(Auth::guard('web')->check()) {
            if(Auth::user()->settings->get('language')) {
                $locale = Auth::user()->settings->get('language')->value;
                Session::put('locale', $locale);
            }
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
