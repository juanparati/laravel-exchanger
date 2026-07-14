<?php
declare(strict_types=1);

namespace Juanparati\LaravelExchanger\Tests\Unit;

use Exchanger\Service\EuropeanCentralBank;
use Juanparati\LaravelExchanger\Exceptions\ExchangerException;
use Juanparati\LaravelExchanger\ExchangerConverter;
use Juanparati\LaravelExchanger\PendingConversion;
use Juanparati\LaravelExchanger\Providers\ExchangerServiceProvider;
use Orchestra\Testbench\TestCase;


/**
 * Class FluentConversionTest.
 *
 * @package Juanparati\LaravelExchanger\Tests\Unit
 */
class FluentConversionTest extends TestCase
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
            EuropeanCentralBank::class => [],
        ]);

        $app['config']->set('exchanger.cache_time', 60);
        $app['config']->set('exchanger.cache_store', 'array');
    }


    /**
     * Test fluent conversion started with from().
     *
     * @throws \Throwable
     */
    public function testFluentConversion() {
        $exchanger = $this->app->make(ExchangerConverter::class);

        // Identical pair (no network required)
        $this->assertEquals(5.0, $exchanger->from('eur')->to('eur')->amount(5)->get());

        // Live ECB conversion
        $eurToDkk = $exchanger->from('eur')->to('dkk')->amount(100)->get();
        $this->assertGreaterThan(0, $eurToDkk);

        // Rounding
        $rounded = $exchanger->from('eur')->to('dkk')->amount(100)->round(2)->get();
        $this->assertEquals(round($eurToDkk, 2), $rounded);

        // Historical rate (ECB only supports EUR-based pairs)
        $historical = $exchanger->from('eur')->to('dkk')->date('2015-04-20')->get();
        $this->assertGreaterThan(0, $historical);
        $this->assertNotEquals($eurToDkk / 100, $historical);

        // Rate object terminal
        $rate = $exchanger->from('eur')->to('dkk')->rate();
        $this->assertGreaterThan(0, $rate->getValue());
        $this->assertEquals('european_central_bank', $rate->getProviderName());
    }


    /**
     * Test that convert() without arguments returns the fluent builder
     * and keeps the classic behavior when called with arguments.
     *
     * @throws \Throwable
     */
    public function testConvertEntryPoint() {
        $exchanger = $this->app->make(ExchangerConverter::class);

        $this->assertInstanceOf(PendingConversion::class, $exchanger->convert());

        $this->assertEquals(
            5.0,
            $exchanger->convert()->from('eur')->to('eur')->amount(5)->get()
        );

        // Classic call style still works
        $this->assertEquals(5.0, $exchanger->convert('eur', 'eur', 5));
    }


    /**
     * Test the per-conversion service selection and state restoration.
     *
     * @throws \Throwable
     */
    public function testUsingRestoresServices() {
        $exchanger = $this->app->make(ExchangerConverter::class);

        $exchanger->attachAll();
        $previousServices = $exchanger->getAttachedServices();

        $result = $exchanger->from('eur')
            ->to('dkk')
            ->using(EuropeanCentralBank::class)
            ->get();

        $this->assertGreaterThan(0, $result);
        $this->assertEquals($previousServices, $exchanger->getAttachedServices());
    }


    /**
     * Test conditional chaining and cache bypass.
     *
     * @throws \Throwable
     */
    public function testConditionalAndCache() {
        $exchanger = $this->app->make(ExchangerConverter::class);

        $amount = $exchanger->from('eur')
            ->to('eur')
            ->when(true, fn (PendingConversion $c) => $c->amount(10))
            ->when(false, fn (PendingConversion $c) => $c->amount(999))
            ->get();

        $this->assertEquals(10.0, $amount);

        $previousCache = $exchanger->getCacheUsage();
        $exchanger->from('eur')->to('dkk')->withoutCache()->get();
        $this->assertEquals($previousCache, $exchanger->getCacheUsage());
    }


    /**
     * Test that missing currencies throw an exception.
     *
     * @throws \Throwable
     */
    public function testMissingCurrenciesThrows() {
        $exchanger = $this->app->make(ExchangerConverter::class);

        $this->expectException(ExchangerException::class);

        $exchanger->from('eur')->get();
    }
}
