<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Horizon::auth(function () {
            return true;
        });
        
        Validator::extend('poly_exists', function ($attribute, $value, $parameters, $validator) {
            if (!$objectType = array_get($validator->getData(), $parameters[0], false)) {
                return false;
            }
        
            return !empty(resolve($objectType)->find($value));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // if (env('APP_ENV') === 'production') {
        //     $this->app->alias('bugsnag.logger', Log::class);
        //     $this->app->alias('bugsnag.logger', LoggerInterface::class);
        // }
    }
}
