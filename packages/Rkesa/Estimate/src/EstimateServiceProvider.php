<?php
namespace Rkesa\Estimate;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Log;

class EstimateServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'estimate');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->publishes([
            __DIR__.'/../resources/assets/images' => public_path('vendor/estimate'),
        ], 'erp-public');
        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make('Rkesa\Estimate\Http\Controllers\EstimateController');
        $this->app->make('Rkesa\Estimate\Http\Controllers\FichaResourceController');
        $this->app->make('Rkesa\Estimate\Http\Controllers\FichaController');
        $this->app->make('Rkesa\Estimate\Http\Controllers\PlanningController');
        $this->app->make('Rkesa\Estimate\Http\Controllers\ResourceController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
