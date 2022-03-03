<?php
namespace Rkesa\Project;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ProjectServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'project');
        $this->publishes([ __DIR__.'/../resources/lang' => resource_path('lang') ], 'lang');
        $this->loadRoutes();
    }

    public function register()
    {
        $this->app->make('Rkesa\Project\Http\Controllers\ProjectController');
    }

    public function loadRoutes()
    {
        require __DIR__.'/routes.php';
    }
}
