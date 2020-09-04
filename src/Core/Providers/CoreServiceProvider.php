<?php

namespace Ahmeti\Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
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
        $this->app->singleton('coreservice', function () {

            $customCoreService = '\App\Modules\Core\Services\CoreService';

            if( class_exists($customCoreService) ){
                return new $customCoreService();
            }

            return new \Ahmeti\Modules\Core\Services\CoreService;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['coreservice'];
    }
}
