<?php
namespace Rkesa\Service;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'service');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make('Rkesa\Service\Http\Controllers\ServiceController');
        $this->app->make('Rkesa\Service\Http\Controllers\ServiceSettingsController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
