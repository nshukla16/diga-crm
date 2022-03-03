<?php

namespace App\Http;

use App\Http\Middleware\CanUseTimetracker;
use App\Http\Middleware\ModulesRegistration;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\ModulesRegistration::class,
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \App\Http\Middleware\AppLocale::class,
        //        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * Disabled, because of REST API, see:
     * https://security.stackexchange.com/questions/166724/should-i-use-csrf-protection-on-rest-api-endpoints
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            //            \App\Http\Middleware\EncryptCookies::class,
            //            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            //            \Illuminate\Session\Middleware\StartSession::class,
            //            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            //            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api_group' => [
            'jwt',
            // 'auth:api',
            \App\Http\Middleware\UserLocale::class,
            \App\Http\Middleware\CheckIfActive::class,
        ]
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'user_can' => \App\Http\Middleware\Roles::class,
        'is_admin' => \App\Http\Middleware\IsAdmin::class,
        'can_use_timetracker' => \App\Http\Middleware\CanUseTimetracker::class,
        'can_view_results_of_timetracker' => \App\Http\Middleware\CanViewResultsOfTimetracker::class,
        'jwt' => \App\Http\Middleware\CheckJWT::class,
        'check.scope' => \App\Http\Middleware\CheckScope::class,
    ];
}
