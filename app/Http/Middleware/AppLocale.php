<?php

namespace App\Http\Middleware;

use App\GlobalSettings;
use Carbon\Carbon;
use Laravelrus\LocalizedCarbon\LocalizedCarbon;
use Closure;
use App;
use Log;
use Auth;

class AppLocale
{
    public function handle($request, Closure $next)
    {
        $locale = GlobalSettings::first()->default_language;

        App::setLocale($locale);

        if($locale == 'en'){
            setlocale(LC_TIME, 'en_GB.utf8');
        } elseif ($locale == 'pt'){
            setlocale(LC_TIME, 'pt_PT.utf8');
        } else {
            $locale = $locale . '_' . strtoupper($locale) . '.utf8';
            setlocale(LC_TIME, $locale);
        }

        return $next($request);
    }
}
