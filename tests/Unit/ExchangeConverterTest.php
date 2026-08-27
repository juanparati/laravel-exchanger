<?php
declare(strict_types=1);

use Exchanger\Service\EuropeanCentralBank;
use Exchanger\Service\NationalBankOfDenmark;
use Exchanger\Service\NationalBankOfRomania;
use Illuminate\Support\Carbon;
use Juanparati\LaravelExchanger\ExchangerConverter;


beforeEach(function () {
    config()->set('exchanger.services', [
        EuropeanCentralBank::class   => [],
        NationalBankOfDenmark::class => [],
        NationalBankOfRomania::class => [],
    ]);

    $this->exchanger = $this->app->make(ExchangerConverter::class);
});


describe('rates and conversions', function () {

    it('returns the amount unchanged for an identical currency pair', function () {
        expect($this->exchanger->from('eur')->to('eur')->amount(1)->getValue())
            ->toBe(1.0);
    });


    it('converts EUR to DKK and back', function () {
        $eurToDkk = $this->exchanger->from('eur')->to('dkk')->amount(1)->getValue();

        expect($eurToDkk)->toBeGreaterThan(0)
            ->and(round($this->exchanger->from('dkk')->to('eur')->amount($eurToDkk)->getValue()))
            ->toEqual(1.0);
    });


    it('converts RON to DKK and back', function () {
        $ronToDkk = $this->exchanger->from('ron')->to('dkk')->amount(100)->getValue();

        expect($ronToDkk)->toBeGreaterThan(0)
            ->and(round($this->exchanger->from('dkk')->to('ron')->amount($ronToDkk)->getValue()))
            ->toEqual(100.0);
    });


    it('converts using a historical rate', function () {
        $historical = $this->exchanger->from('eur')
            ->to('pln')
            ->amount(3)
            ->date(Carbon::createFromDate(2015, 4, 20))
            ->getValue();

        expect($historical)->toEqualWithDelta(3 * 3.9891, 1e-6);
    });
});


describe('last exchange rate result', function () {

    it('reports the provider that resolved the rate', function () {
        expect($this->exchanger->getRate('eur', 'pln')->getValue())->toBeGreaterThan(0)
            ->and($this->exchanger->getLastExchangeRateResult()->getProviderName())
            ->toBe('european_central_bank');
    });


    it('falls back to the next provider in the chain after a detach', function () {
        $this->exchanger->detach(NationalBankOfDenmark::class);

        expect($this->exchanger->getRate('ron', 'pln')->getValue())->toBeGreaterThan(0)
            ->and($this->exchanger->getLastExchangeRateResult()->getProviderName())
            ->toBe('national_bank_of_romania');
    });


    it('retrieves historical rates', function () {
        $rate = $this->exchanger->getRate('eur', 'usd', Carbon::createFromDate(2024, 3, 15));

        expect($rate->getValue())->toEqualWithDelta(1.0892, 1e-6);
    });
});


describe('service attachment', function () {

    it('uses only the attached service after a detach all', function () {
        $this->exchanger->detachAll()->attach(NationalBankOfDenmark::class);

        $rate = $this->exchanger->getRate('pln', 'dkk');

        expect($rate->getValue())->toBeGreaterThan(0)
            ->and($rate->getProviderName())->toBe('national_bank_of_denmark');
    });
});
