<?php
namespace Rkesa\Planning;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PlanningServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'gantt');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->loadRoutes();
    }

    public function register()
    {
//        $this->app->make('Rkesa\Planning\Http\Controllers\ProjectController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}