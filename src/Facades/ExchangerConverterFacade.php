<?php


namespace Juanparati\LaravelExchanger\Facades;


use Illuminate\Support\Facades\Facade;
use Juanparati\LaravelExchanger\ExchangerConverter;


/**
 * Class ExchangeConverter.
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