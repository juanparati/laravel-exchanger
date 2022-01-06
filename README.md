![](https://api.travis-ci.com/juanparati/laravel-exchanger.svg?branch=master)

# Laravel-Exchanger

A Laravel currency converter library that uses [florianv/exchanger](https://github.com/florianv/exchanger).


## Installation

    composer require juanparati/laravel-exchanger

Facade registration (optional):

    'aliases' => [
        ...
        'CurrencyExchanger' => \Juanparati\LaravelExchanger\Facades\ExchangerConverterFacade,
        ...
    ]


## Configuration

Publish configuration file:

    artisan vendor:publish --provider="Juanparati\LaravelExchanger\Providers\ExchangerServiceProvider"

The configuration contains a list of services, check the [florianv/exchanger](https://github.com/florianv/exchanger) documentation in order to know the description and characteristics of each service.

It's important to provide a valid cache time (in seconds) in order to avoid duplicate requests.


## Usage


### Get the currency rate

    $rate = CurrencyExchanger::getRate('eur', 'pln'); // Return Exchanger\ExchangeRate
    $rate->getValue();                                // Returns rate as float
    $rate->getDate()->format('Y-m-d')                 // Returns exchange date

    // Historical rate
    CurrencyExchanger::getRate('nok', 'sek', now()->subDays(10));


### Convert currency

    CurrencyExchanger::convert('ron', 'dkk', 10); // Convert 10 RON to DKK and return as float

    // Historical conversion rate 
    CurrencyExchanger::convert('ron', 'dkk', 10, now()->subDays(5));

    // Obtain the last rate (Exchanger\ExchangeRate) for the previous currency conversion
    CurrencyExchanger::getLastExchangeRateResult();


### Cache state

Is sometimes convenient to disable the cache in order of force to request the most recent rate or conversion. In order to achieve that is possible to disable temporally the cache:

    CurrencyExchanger::setCacheStatus(false); // Cache disabled
    CurrencyExchanger::setCacheStatus(true);  // Cache enabled

Remember that cache is always enabled by default when the configuration key "cache_time" has a valid integer.


### Attach/Detach services on-demand

It's possible to attach and detach services on demand:

    // Detach service
    CurrencyExchanger::detach(\Exchanger\Service\Cryptonator::class);

    // Attach service
    CurrencyExchanger::attach(\Exchanger\Service\Cryptonator::class);

By default all the services registered into the configuration are attached by default.


### Execute custom queries

Because this library works as a wrapper for [florianv/exchanger](https://github.com/florianv/exchanger) it's possible to execute custom queries passing the build query to the "executeQuery" method.

    ...
    CurrencyExchanger::executeQuery($query->build);
    ...
