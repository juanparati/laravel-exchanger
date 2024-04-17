<?php
declare(strict_types=1);

namespace Juanparati\LaravelExchanger\Tests\Unit;

use Exchanger\Service\Fixer;
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
            \Exchanger\Service\Fixer::class                 => ['access_key' => env('FIXER_KEY'), 'enterprise' => true],
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

        // Test DKK to EUR (FIXER)
        $this->assertEquals(1, round($exchanger->convert('dkk', 'eur', $eurToDkk)));

        // Test RON to DKK
        $ronToDKK = $exchanger->convert('ron', 'dkk', 100);
        $this->assertGreaterThan(0, $ronToDKK);

        // Test DKK to RON
        $this->assertEquals(100, round($exchanger->convert('dkk', 'ron', $ronToDKK)));

        // Test historical PLN to NOK
        $this->assertEquals(
            1.4158950000000001,
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

        $exchanger->detach(Fixer::class);

        $this->assertGreaterThan(0, $exchanger->getRate('ron', 'pln')->getValue());
        $this->assertEquals('national_bank_of_romania', $exchanger->getLastExchangeRateResult()->getProviderName());

        $this->assertEquals(1.0892, $exchanger->getRate(
            'eur', 'usd',
            Carbon::createFromDate(2024, 3, 15)
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

        $exchanger->attach(Fixer::class);

        $rate = $exchanger->getRate('pln', 'sek');

        $this->assertGreaterThan(0, $rate->getValue());
        $this->assertEquals('fixer', $rate->getProviderName());
    }
}
