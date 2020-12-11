<?php

/**
 * Configuration file for Laravel Exchanger.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    |
    | List of services to use sorted by registration sequence.
    | The first registered service is going to be used as the mainly one using
    | the following ones as fallback.
    |
    | A complete list of available services is available at:
    | https://github.com/florianv/exchanger
    |
    */
    'services' => [
        // \Exchanger\Service\Fixer::class                       => ['access_key' => 'YOUR_KEY'],
        // \Exchanger\Service\CurrencyLayer::class               => ['access_key' => 'access_key', 'enterprise' => false],
        // \Exchanger\Service\CoinLayer::class                   => ['access_key' => 'access_key', 'paid' => false],
        \Exchanger\Service\EuropeanCentralBank::class         => [],
        \Exchanger\Service\ExchangeRatesApi::class            => [],
        \Exchanger\Service\NationalBankOfRomania::class       => [],
        \Exchanger\Service\CentralBankOfRepublicTurkey::class => [],
        \Exchanger\Service\CentralBankOfCzechRepublic::class  => [],
        \Exchanger\Service\RussianCentralBank::class          => [],
        // \Exchanger\Service\Forge::class                       => ['api_key' => 'api_key'],
        // \Exchanger\Service\Cryptonator::class                 => [],
        // \Exchanger\Service\CurrencyDataFeed::class            => ['api_key' => 'api_key'],
        // \Exchanger\Service\OpenExchangeRates::class           => ['app_id' => 'app_id', 'enterprise' => false],
        // \Exchanger\Service\Xignite::class                     => ['token' => 'token'],
        // \Exchanger\Service\PhpArray::class                    => []
    ],


    /*
    |--------------------------------------------------------------------------
    | Cache preferences
    |--------------------------------------------------------------------------
    |
    */
    'cache_time'       => null,                 // Cache time in seconds (null = no cache).
    'cache_store'      => 'default',            // Cache store to use.
    'cache_prefix'     => 'Exchanger:Currency', // Cache prefix.

];
