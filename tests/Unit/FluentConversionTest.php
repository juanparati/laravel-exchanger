<?php
declare(strict_types=1);

use Exchanger\Service\EuropeanCentralBank;
use Juanparati\LaravelExchanger\Exceptions\ExchangerException;
use Juanparati\LaravelExchanger\ExchangerConverter;
use Juanparati\LaravelExchanger\PendingConversion;


beforeEach(function () {
    config()->set('exchanger.services', [
        EuropeanCentralBank::class => [],
    ]);

    config()->set('exchanger.cache_time', 60);
    config()->set('exchanger.cache_store', 'array');

    $this->exchanger = $this->app->make(ExchangerConverter::class);
});


describe('fluent conversion', function () {

    it('converts an identical currency pair without network access', function () {
        expect($this->exchanger->from('eur')->to('eur')->amount(5)->getValue())
            ->toBe(5.0);
    });


    it('converts using a live rate and rounds on demand', function () {
        $eurToDkk = $this->exchanger->from('eur')->to('dkk')->amount(100)->getValue();

        expect($eurToDkk)->toBeGreaterThan(0)
            ->and($this->exchanger->from('eur')->to('dkk')->amount(100)->round(2)->getValue())
            ->toEqual(round($eurToDkk, 2));
    });


    it('converts using a historical rate', function () {
        $current    = $this->exchanger->from('eur')->to('dkk')->getValue();
        $historical = $this->exchanger->from('eur')->to('dkk')->date('2015-04-20')->getValue();

        expect($historical)->toBeGreaterThan(0)
            ->and($historical)->not->toEqual($current);
    });


    it('exposes the rate object as a terminal', function () {
        $rate = $this->exchanger->from('eur')->to('dkk')->rate();

        expect($rate->getValue())->toBeGreaterThan(0)
            ->and($rate->getProviderName())->toBe('european_central_bank');
    });
});


describe('entry points', function () {

    it('starts a fluent conversion from convert()', function () {
        expect($this->exchanger->convert())->toBeInstanceOf(PendingConversion::class)
            ->and($this->exchanger->convert()->from('eur')->to('eur')->amount(5)->getValue())
            ->toBe(5.0);
    });
});


describe('per-conversion state', function () {

    it('restores the attached services after using()', function () {
        $this->exchanger->attachAll();
        $previousServices = $this->exchanger->getAttachedServices();

        $result = $this->exchanger->from('eur')
            ->to('dkk')
            ->using(EuropeanCentralBank::class)
            ->getValue();

        expect($result)->toBeGreaterThan(0)
            ->and($this->exchanger->getAttachedServices())->toBe($previousServices);
    });


    it('applies conditional chaining with when()', function () {
        $amount = $this->exchanger->from('eur')
            ->to('eur')
            ->when(true, fn (PendingConversion $c) => $c->amount(10))
            ->when(false, fn (PendingConversion $c) => $c->amount(999))
            ->getValue();

        expect($amount)->toBe(10.0);
    });


    it('restores the cache usage after withoutCache()', function () {
        $previousCache = $this->exchanger->getCacheUsage();

        $this->exchanger->from('eur')->to('dkk')->withoutCache()->getValue();

        expect($this->exchanger->getCacheUsage())->toBe($previousCache);
    });
});


it('throws when the currencies are missing', function () {
    $this->exchanger->from('eur')->getValue();
})->throws(ExchangerException::class);
