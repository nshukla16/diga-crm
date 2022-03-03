<?php

namespace App\Providers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') === 'production') {
            \URL::forceScheme('https');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \Auth0\Login\Contract\Auth0UserRepository::class,
            \App\Repositories\CustomUserRepository::class
        );

        if ($this->app->environment('local')) {
            // Dev only service providers
        } elseif ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
        app('Dingo\Api\Exception\Handler')->register(function (AuthenticationException $exception) {
            return Response(['error' => 'Unauthenticated.'], 401);
        });
    }
}
