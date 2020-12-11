<?php
namespace Juanparati\LaravelExchanger\Tests\Unit;

use Exchanger\Service\ExchangeRatesApi;
use Illuminate\Support\Carbon;
use Juanparati\LaravelExchanger\ExchangerConverter;
use Juanparati\LaravelExchanger\Providers\ExchangerServiceProvider;
use Orchestra\Testbench\TestCase;


/**
 * Class ExchangeConverterTest.
 *
 * @package Juanparati\LaravelExchanger\Tests\Unit
 */
class ExchangeConverterTest extends TestCase
{

    /**
     * Load service providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return string[]
     */
    protected function getPackageProviders($app)
    {
        return [ExchangerServiceProvider::class];
    }



    /**
     * Prepare the environment and configuration.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app) {
        $app['config']->set('exchanger.services', [
            \Exchanger\Service\EuropeanCentralBank::class   => [],
            \Exchanger\Service\ExchangeRatesApi::class      => [],
            \Exchanger\Service\NationalBankOfRomania::class => [],
        ]);
    }


    /**
     * Test basic rate and conversion.
     *
     * @throws \Exchanger\Exception\ChainException
     * @throws \Throwable
     */
    public function testRateAndConversion() {
        $exchanger = $this->app->make(ExchangerConverter::class);

        // Test equal equivalence
        $this->assertEquals(1, $exchanger->convert('eur', 'eur', 1));

        // Test EUR to DKK (European Central bank)
        $eurToDkk = $exchanger->convert('eur', 'dkk', 1);
        $this->assertGreaterThan(0, $eurToDkk);

        // Test DKK to EUR (Exchange Rates Api)
        $this->assertEquals(1, round($exchanger->convert('dkk', 'eur', $eurToDkk)));

        // Test RON to DKK
        $ronToDKK = $exchanger->convert('ron', 'dkk', 100);
        $this->assertGreaterThan(0, $ronToDKK);

        // Test DKK to RON
        $this->assertEquals(100, round($exchanger->convert('dkk', 'ron', $ronToDKK)));

        // Test historical PLN to NOK
        $this->assertEquals(
            3 * 0.4725302061,
            $exchanger->convert('nok', 'pln', 3, Carbon::createFromDate(2015, 4, 20))
        );
    }


    /**
     * Test if the last exchange results correspond with the last exchange rate result.
     *
     * @throws \Exchanger\Exception\ChainException
     * @throws \Throwable
     */
    public function testLastExchangeRate() {
        $exchanger = $this->app->make(ExchangerConverter::class);

        $this->assertGreaterThan(0, $exchanger->getRate('eur', 'pln')->getValue());
        $this->assertEquals('european_central_bank', $exchanger->getLastExchangeRateResult()->getProviderName());

        $exchanger->detach(ExchangeRatesApi::class);

        $this->assertGreaterThan(0, $exchanger->getRate('ron', 'pln')->getValue());
        $this->assertEquals('national_bank_of_romania', $exchanger->getLastExchangeRateResult()->getProviderName());

        $this->assertEquals(1.2043, $exchanger->getRate(
            'eur', 'usd',
            Carbon::createFromDate(2015, 1, 2)
        )->getValue());

    }


    /**
     * Test detach and attach.
     *
     * @throws \Exchanger\Exception\ChainException
     * @throws \Throwable
     */
    public function testDetachAndAttach() {
        $exchanger = $this->app->make(ExchangerConverter::class);

        $exchanger->detachAll();

        $exchanger->attach(ExchangeRatesApi::class);

        $rate = $exchanger->getRate('pln', 'sek');

        $this->assertGreaterThan(0, $rate->getValue());
        $this->assertEquals('exchange_rates_api', $rate->getProviderName());
    }

}