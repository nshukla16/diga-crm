<?php
namespace Rkesa\Email;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'email');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make('Rkesa\Email\Http\Controllers\EmailController');
        $this->app->make('Rkesa\Email\Http\Controllers\DomainController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
