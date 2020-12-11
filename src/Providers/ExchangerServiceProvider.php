<?php

namespace Juanparati\LaravelExchanger\Providers;

use Illuminate\Support\ServiceProvider;
use Juanparati\LaravelExchanger\ExchangerConverter;


/**
 * Class ECBEServiceProvider.
 *
 * @package Juanparati\LaravelECBE
 */
class ExchangerServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap service.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/exchanger.php' => config_path('exchanger.php'),
        ]);
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/exchanger.php', 'exchanger'
        );


        $this->app->singleton(ExchangerConverter::class, function () {
            return new ExchangerConverter(config('exchanger'));
        });
    }

}