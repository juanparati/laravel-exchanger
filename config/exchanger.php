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

        // Services that do not require credentials.
        \Exchanger\Service\EuropeanCentralBank::class              => [],
        \Exchanger\Service\NationalBankOfRomania::class            => [],
        \Exchanger\Service\CentralBankOfRepublicTurkey::class      => [],
        \Exchanger\Service\CentralBankOfCzechRepublic::class       => [],
        // \Exchanger\Service\BulgarianNationalBank::class            => [],
        // \Exchanger\Service\CentralBankOfRepublicUzbekistan::class  => [],
        // \Exchanger\Service\NationalBankOfGeorgia::class            => [],
        // \Exchanger\Service\NationalBankOfRepublicBelarus::class    => [],
        // \Exchanger\Service\NationalBankOfUkraine::class            => [],
        // \Exchanger\Service\RussianCentralBank::class               => [],
        // \Exchanger\Service\WebserviceX::class                      => [],

        // Services that require API credentials.
        // \Exchanger\Service\AbstractApi::class                      => ['api_key' => ''],
        // \Exchanger\Service\ApiLayer\CurrencyData::class            => ['api_key' => ''],
        // \Exchanger\Service\ApiLayer\ExchangeRatesData::class       => ['api_key' => ''],
        // \Exchanger\Service\ApiLayer\Fixer::class                   => ['api_key' => ''],
        // \Exchanger\Service\CoinLayer::class                        => ['access_key' => '', 'paid' => false],
        // \Exchanger\Service\CurrencyConverter::class                => ['access_key' => '', 'enterprise' => false],
        // \Exchanger\Service\CurrencyDataFeed::class                 => ['api_key' => ''],
        // \Exchanger\Service\CurrencyLayer::class                    => ['access_key' => '', 'enterprise' => false],
        // \Exchanger\Service\ExchangerateHost::class                 => [],
        // \Exchanger\Service\ExchangeRatesApi::class                 => ['access_key' => '', 'enterprise' => false],
        // \Exchanger\Service\FastForex::class                        => ['api_key' => ''],
        // \Exchanger\Service\Fixer::class                            => ['access_key' => '', 'enterprise' => false],
        // \Exchanger\Service\FixerApiLayer::class                    => ['api_key' => ''],
        // \Exchanger\Service\Forge::class                            => ['api_key' => ''],
        // \Exchanger\Service\OpenExchangeRates::class                => ['app_id' => '', 'enterprise' => false],
        // \Exchanger\Service\XchangeApi::class                       => ['api-key' => ''],
        // \Exchanger\Service\Xignite::class                          => ['token' => ''],
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
