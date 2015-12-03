<?php

namespace Asimov\Solaria\Widgets\Forms;

use Illuminate\Support\ServiceProvider;
use Route;

class FormsModuleServiceProvider extends ServiceProvider{

    public function boot(){
        $this->registerRoutes();
        $this->registerViews();
        $this->publishMigrationsAndSeeds();
        $this->publishAssets();
    }

    /**
     * Registra una instancia del widget en la aplicaciÃ³n
     *
     * @return void
     */
    public function register() {
        $widgetLoader = $this->app->make('solaria.widgetloader');
        $widgetLoader->add(new Forms());
    }

    /**
     * Registra las rutas del widget
     */
    private function registerRoutes() {
        Route::group(['middleware' => 'auth', 'prefix' => 'backend', 'namespace' => 'Asimov\Solaria\Widgets\Forms\Http\Controllers'], function() {
            Route::controller('/widgets/forms', 'FormsController');
        });
    }

    /**
     * Registra las vistas del widget
     */
    private function registerViews() {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'widgetforms');
    }

    /**
     * Publica las migraciones del widget
     */
    private function publishMigrationsAndSeeds() {
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Publica los assets del widget
     */
    private function publishAssets(){
        $this->publishes([
            __DIR__ . '/public/' => public_path('widgets/forms')
        ], 'assets');
    }
}