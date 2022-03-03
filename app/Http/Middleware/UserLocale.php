<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Log;
use Carbon\Carbon;
use Laravelrus\LocalizedCarbon\LocalizedCarbon;
use Auth;
use Sentry;

class UserLocale
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $locale = $user->site_language;

            App::setLocale($locale);

            if($locale == 'en'){
                setlocale(LC_TIME, 'en_GB.utf8');
            } elseif ($locale == 'pt'){
                setlocale(LC_TIME, 'pt_PT.utf8');
            } else {
                $locale = $locale . '_' . strtoupper($locale) . '.utf8';
                setlocale(LC_TIME, $locale);
            }

            // Set user for sentry
            app('sentry')->configureScope(function (Sentry\State\Scope $scope) use($user): void {
                $scope->setUser(['id' => $user->id, 'email' => $user->email, 'username' => $user->name]);
            });
        }
        return $next($request);
    }
}
