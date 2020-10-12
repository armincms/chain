<?php

namespace Armincms\Fields;
 
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class ChainServiceProvider extends ServiceProvider 
{   
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('chain', __DIR__.'/../dist/js/field.js'); 

        $this->app->booted(function() {
            $this->routes();
        });
    }  

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    { 
        \Route::middleware(['nova'])
                ->prefix('/nova-api') 
                ->namespace(__NAMESPACE__.'\\Http\\Controllers')
                ->group(function($router) {
                    $router->post('/{resource}/chain-fields', 'ChainFieldController@handle');
                });                
    } 
}
