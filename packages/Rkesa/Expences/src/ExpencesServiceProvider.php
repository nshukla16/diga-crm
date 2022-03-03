<?php
namespace Rkesa\Expences;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ExpencesServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'expences');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make('Rkesa\Expences\Http\Controllers\ExpencesController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
