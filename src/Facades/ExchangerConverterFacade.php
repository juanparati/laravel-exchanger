<?php


namespace Juanparati\LaravelExchanger\Facades;


use Illuminate\Support\Facades\Facade;
use Juanparati\LaravelExchanger\ExchangerConverter;


/**
 * Class ExchangeConverter.
 *
 * @method static float|\Juanparati\LaravelExchanger\PendingConversion convert(?string $fromCurrency = null, ?string $toCurrency = null, $value = null, ?\DateTimeInterface $rateDate = null)
 * @method static \Juanparati\LaravelExchanger\PendingConversion from(string $currency)
 * @method static \Exchanger\Contract\ExchangeRate getRate(string $fromCurrency, string $toCurrency, ?\DateTimeInterface $rateDate = null)
 * @method static \Exchanger\Contract\ExchangeRate|null getLastExchangeRateResult()
 * @method static \Juanparati\LaravelExchanger\ExchangerConverter attach(...$services)
 * @method static \Juanparati\LaravelExchanger\ExchangerConverter detach(...$services)
 * @method static \Juanparati\LaravelExchanger\ExchangerConverter attachAll()
 * @method static \Juanparati\LaravelExchanger\ExchangerConverter detachAll()
 * @method static \Juanparati\LaravelExchanger\ExchangerConverter setCacheUsage(bool $active)
 *
 * @see \Juanparati\LaravelExchanger\ExchangerConverter
 *
 * @package Facades
 */
class ExchangerConverterFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ExchangerConverter::class;
    }
}