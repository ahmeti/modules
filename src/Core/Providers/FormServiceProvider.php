<?php

namespace Ahmeti\Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;

class FormServiceProvider extends ServiceProvider
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
        $this->app->singleton('formservice', function () {

            $customFormService = '\App\Modules\Core\Services\FormService';

            if( class_exists($customFormService) ){
                return new $customFormService();
            }

            return new \Ahmeti\Modules\Core\Services\FormService;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['formservice'];
    }
}
