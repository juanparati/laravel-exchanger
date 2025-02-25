<?php


namespace Juanparati\LaravelExchanger\CacheStorageInterfaces;


use Illuminate\Contracts\Cache\Repository as Cache;
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
     */
    protected int $defaultCacheTime = 0;


    /**
     * ExchangerLaravelCacheInterface constructor.
     *
     * @param Cache $cache
     * @param string $cachePrefix
     */
    public function __construct(Cache $cache, protected string $cachePrefix = '')
    {
        parent::__construct($cache);
    }


    /**
     * Fetch data from cache.
     *
     * @param string $key.
     * @return CacheEntry|void|null
     */
    public function fetch($key)
    {
        return parent::fetch($this->cachePrefix . $key);
    }


    /**
     * Save data in cache.
     *
     * @param string $key
     * @param CacheEntry $data
     * @return bool
     */
    public function save($key, CacheEntry $data): bool
    {
        return parent::save($this->cachePrefix . $key, $data);
    }


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
