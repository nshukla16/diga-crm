<?php
namespace Rkesa\Client;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ClientServiceProvider extends ServiceProvider
{

    protected $namespace = 'Diga\Client';

    public function boot(\Illuminate\Routing\Router $router)
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'client');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'client');
        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make('Rkesa\Client\Http\Controllers\AruController');
        $this->app->make('Rkesa\Client\Http\Controllers\ClientController');
        $this->app->make('Rkesa\Client\Http\Controllers\ClientSettingsController');
        $this->app->make('Rkesa\Client\Http\Controllers\ContactController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
