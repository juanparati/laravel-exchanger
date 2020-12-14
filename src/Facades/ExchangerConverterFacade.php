<?php


namespace Juanparati\LaravelExchanger\Facades;


use Illuminate\Support\Facades\Facade;


/**
 * Class ExchangeConverter.
 *
 * @package Facades
 */
class ExchangerConverterFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'exchanger';
    }
}