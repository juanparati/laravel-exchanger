[![Tests](https://github.com/juanparati/laravel-exchanger/actions/workflows/tests.yml/badge.svg)](https://github.com/juanparati/laravel-exchanger/actions/workflows/tests.yml)

# Laravel-Exchanger

A Laravel currency converter library that uses [florianv/exchanger](https://github.com/florianv/exchanger).


## Installation

    composer require juanparati/laravel-exchanger

## Configuration

Publish the configuration file:

    artisan vendor:publish --provider="Juanparati\LaravelExchanger\Providers\ExchangerServiceProvider"

The configuration contains a list of services, check the [florianv/exchanger](https://github.com/florianv/exchanger) documentation in order to know the description and characteristics of each service.

It's important to provide a valid cache time (in seconds) in order to avoid duplicate requests.


## Usage


### Get the currency rate

    $rate = Exchanger::getRate('eur', 'pln'); // Return Exchanger\ExchangeRate
    $rate->getValue();                                // Returns rate as float
    $rate->getDate()->format('Y-m-d')                 // Returns exchange date

    // Historical rate
    Exchanger::getRate('nok', 'sek', now()->subDays(10));


### Convert currency

Conversions are expressed fluently, starting the chain with "from" or with "convert":

    Exchanger::from('usd')
        ->to('eur')
        ->amount(100)
        ->round(2)
        ->getValue();                 // Converted amount as float

    Exchanger::convert()
        ->from('usd')
        ->to('eur')
        ->getValue();                 // Amount defaults to 1, so it returns the rate

Historical rates are available through the "date" method (accepts a date string or a DateTimeInterface):

    Exchanger::from('nok')->to('sek')->date('2020-01-01')->getValue();

Use "rate" instead of "getValue" in order to obtain the Exchanger\ExchangeRate object:

    $rate = Exchanger::from('eur')->to('dkk')->rate();
    $rate->getValue();
    $rate->getProviderName();

Additional methods:

    Exchanger::from('eur')
        ->to('dkk')
        ->using(\Exchanger\Service\EuropeanCentralBank::class) // Use only the given services for this conversion
        ->withoutCache()                                       // Skip the cache for this conversion
        ->when($someCondition, fn ($c) => $c->round(2))        // Conditional chaining (also "unless")
        ->getValue();

The "using" and "withoutCache" options only apply to the current conversion; the attached services and cache state are restored afterwards.

The last rate (Exchanger\ExchangeRate) used by a conversion is available afterwards:

    Exchanger::getLastExchangeRateResult();


### Cache state

Is sometimes convenient to disable the cache in order of force to request the most recent rate or conversion. In order to achieve that is possible to disable temporally the cache:

    Exchanger::setCacheUsage(false); // Cache disabled
    Exchanger::setCacheUsage(true);  // Cache enabled

Remember that cache is always enabled by default when the configuration key "cache_time" has a valid integer.


### Attach/Detach services on-demand

It's possible to attach and detach services on demand:

    // Detach service
    Exchanger::detach(\Exchanger\Service\Cryptonator::class);

    // Attach service
    Exchanger::attach(\Exchanger\Service\Cryptonator::class);

By default, all the services registered into the configuration are attached by default.


### Execute custom queries

Because this library works as a wrapper for [florianv/exchanger](https://github.com/florianv/exchanger) it's possible to execute custom queries passing the build query to the "executeQuery" method.

    ...
    Exchanger::executeQuery($query->build());
    ...
