<?php
namespace Rkesa\Dashboard;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

class DashboardServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'dashboard');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->publishes([
            __DIR__.'/Http/config/dashboard.php' => config_path('dashboard.php')
        ], 'erp-config');
        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make(EloquentFactory::class)->load(__DIR__.'/database/factories/');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
