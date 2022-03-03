<?php
namespace Rkesa\Hr;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class HrServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'hr');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make('Rkesa\Hr\Http\Controllers\HrController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
