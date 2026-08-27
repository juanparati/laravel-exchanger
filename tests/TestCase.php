<?php
declare(strict_types=1);

namespace Juanparati\LaravelExchanger\Tests;

use Juanparati\LaravelExchanger\Providers\ExchangerServiceProvider;


/**
 * Class TestCase.
 *
 * Base test case for the package tests.
 *
 * @package Juanparati\LaravelExchanger\Tests
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
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
}
