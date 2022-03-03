<?php
namespace Rkesa\Calendar;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Rkesa\Calendar\Http\Resources\EventResource;

class CalendarServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'calendar');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->loadRoutes();
        EventResource::withoutWrapping();
    }

    public function register()
    {
        $this->app->make('Rkesa\Calendar\Http\Controllers\CalendarController');
        $this->app->make('Rkesa\Calendar\Http\Controllers\CalendarSettingsController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
