<?php
namespace Rkesa\CalendarExtended;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CalendarExtendedServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'calendar_extended');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make('Rkesa\CalendarExtended\Http\Controllers\CalendarExtendedController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
