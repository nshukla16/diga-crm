<?php
namespace Rkesa\EstimateFork;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EstimateForkServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'estimate_fork');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make('Rkesa\EstimateFork\Http\Controllers\EstimateForkController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
