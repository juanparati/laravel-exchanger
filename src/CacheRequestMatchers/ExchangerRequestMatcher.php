<?php
namespace Juanparati\LaravelExchanger\CacheRequestMatchers;

use Kevinrob\GuzzleCache\Strategy\Delegate\RequestMatcherInterface;
use Psr\Http\Message\RequestInterface;


/**
 * Class ExchangerRequestMatcher.
 *
 * @package Juanparati\LaravelExchanger\CacheRequestMatchers
 */
class ExchangerRequestMatcher implements RequestMatcherInterface
{

    /**
     * Cache status.
     *
     * @var bool
     */
    protected bool $useCache = false;


    /**
     * Decides when to use the cache.
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function matches(RequestInterface $request) : bool
    {
        return $this->getCacheStatus();
    }


    /**
     * Set the cache status.
     *
     * @param bool $active
     */
    public function setCacheStatus(bool $active) : void {
        $this->useCache = $active;
    }


    /**
     * Return if the cache status (true = active, false = non-active).
     *
     * @return bool
     */
    public function getCacheStatus() : bool {
        return $this->useCache;
    }
}
