<?php


namespace Juanparati\LaravelExchanger\CacheStorageInterfaces;


use Kevinrob\GuzzleCache\CacheEntry;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;


/**
 * Class ExchangerLaravelCacheInterface.
 *
 * @package Juanparati\LaravelExchanger\CacheStorageInterfaces
 */
class ExchangerLaravelCacheInterface extends LaravelCacheStorage
{

    /**
     * Cache time in seconds.
     *
     * @var int
     */
    protected $defaultCacheTime = 0;


    /**
     * Set the default cache time.
     *
     * @param int $cacheTime
     * @return $this
     */
    public function setDefaultCacheTime(int $cacheTime) : ExchangerLaravelCacheInterface {
        $this->defaultCacheTime = $cacheTime;

        return $this;
    }


    /**
     * Return the cache time.
     *
     * @param CacheEntry $data
     * @return float|int
     */
    protected function getLifeTime(CacheEntry $data)
    {
        return $this->defaultCacheTime;
    }

}