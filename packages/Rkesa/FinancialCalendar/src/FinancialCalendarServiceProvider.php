<?php
namespace Rkesa\FinancialCalendar;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FinancialCalendarServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'financialCalendar');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make('Rkesa\FinancialCalendar\Http\Controllers\FinancialCalendarController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
