<?php

namespace Ahmeti\Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('responseservice', function () {

            $customResponseService = '\App\Modules\Core\Services\ResponseService';

            if( class_exists($customResponseService) ){
                return new $customResponseService();
            }

            return new \Ahmeti\Modules\Core\Services\ResponseService;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['responseservice'];
    }
}
