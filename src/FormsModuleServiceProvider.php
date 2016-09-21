<?php

namespace Asimov\Solaria\Modules\Forms;

use App;
use Asimov\Solaria\Modules\Forms\Models\FormConnector;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Support\ServiceProvider;
use Route;

class FormsModuleServiceProvider extends ServiceProvider{

    public function boot(GateContract $gate, DispatcherContract $events){
        $this->registerRoutes();
        $this->registerViews();
        $this->publishMigrationsAndSeeds();
        $this->publishAssets();
        $this->registerPolicies($gate);
        $this->registerEvents($events);
    }

    /**
     * Registra una instancia del modulo en la aplicacion
     *
     * @return void
     */
    public function register() {
        $moduleLoader = $this->app->make('solaria.moduleloader');
        $moduleLoader->add(new Forms());
    }

    /**
     * Registra las rutas del modulo
     */
    private function registerRoutes() {
        Route::group(['middleware' => 'auth', 'namespace' => 'Asimov\Solaria\Modules\Forms\Http\Controllers'], function() {
            Route::controller('/backend/modules/forms', 'FormsController');
        });
        Route::group(['namespace' => 'Asimov\Solaria\Modules\Forms\Http\Controllers'], function() {
            Route::controller('/modules/forms', 'PublicFormsController');
        });
    }

    /**
     * Registra las vistas del modulo
     */
    private function registerViews() {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'moduleforms');
    }

    /**
     * Publica las migraciones del modulo
     */
    private function publishMigrationsAndSeeds() {
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Publica los assets del modulo
     */
    private function publishAssets(){
        $this->publishes([
            __DIR__ . '/public/' => public_path('modules/forms')
        ], 'assets');
    }

    /**
     * @param GateContract $gate
     */
    private function registerPolicies($gate){
        $gate->define('module_forms_view_results', function($user){
            return $user->hasRole('supervisor|ejecutivo');
        });

        $gate->define('module_forms_assign_user_results', function($user, $results){
            if($user->hasRole('supervisor'))
                return true;

            if($results){
                foreach ($results as $result) {
                    if($user->id != $result->assigned_user_id)
                        return false;
                }
                return true;
            }
            return false;
        });
    }

    private function registerEvents(DispatcherContract $events){
        $events->listen('moduleforms.pre-send', function ($formResults) {
            $connectors = FormConnector::where([
                'form_id' => $formResults->form_id,
                'site_id' => App::make('site')->id,
                'event' => 'pre-send'
            ])->get();
            foreach ($connectors as $connector) {
                $eventHandler = new $connector->content_type();
                $eventHandler->moduleFormPreSend($formResults);
            }
        });
    }
}