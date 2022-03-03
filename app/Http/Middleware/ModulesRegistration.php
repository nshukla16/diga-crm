<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Auth;
use Exception;
use App\GlobalSettings;

class ModulesRegistration
{
    public function handle($request, Closure $next)
    {
        $gs = GlobalSettings::first();
        if ($gs == null){
            throw new Exception('GlobalSettings is empty. Maybe you forgot to make `php artisan seed:initial_with_language en`?');
        }

        $modules = App\Module::all();
        $modules_array = [];
        foreach($modules as $module){
            $modules_array[$module->name] = $module->enabled;
        }
        config(['modules_enabled' => $modules_array]);

        return $next($request);
    }
}
