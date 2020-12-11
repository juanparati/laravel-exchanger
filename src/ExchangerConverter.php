<?php
declare(strict_types=1);

namespace Juanparati\LaravelExchanger;


use Exchanger\Contract\ExchangeRate;
use Exchanger\Contract\ExchangeRateQuery;
use Exchanger\CurrencyPair;
use Exchanger\ExchangeRateQueryBuilder;
use Exchanger\Service\Chain;
use Exchanger\Service\Service;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Juanparati\LaravelExchanger\CacheRequestMatchers\ExchangerRequestMatcher;
use Juanparati\LaravelExchanger\CacheStorageInterfaces\ExchangerLaravelCacheInterface;
use Juanparati\LaravelExchanger\Exceptions\ExchangerException;
use Kevinrob\GuzzleCache\Strategy\Delegate\DelegatingCacheStrategy;
use Kevinrob\GuzzleCache\Strategy\NullCacheStrategy;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Kevinrob\GuzzleCache\CacheMiddleware;


/**
 * Class ExchangeConverter.
 *
 * @package Juanparati\LaravelExchanger
 */
class ExchangerConverter
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $config = [];


    /**
     * HTTP Client.
     *
     * @var Client
     */
    protected $httpClient;


    /**
     * Service list
     * @var Service[]
     */
    protected $serviceList = [];


    /**
     * Chain of services.
     *
     * @var Chain
     */
    protected $exchanger;


    /**
     * Cache request matcher instance.
     *
     * @var ExchangerRequestMatcher
     */
    protected $cacheMatcher;


    /**
     * The last exchange rate result.
     *
     * @var ExchangeRate
     */
    protected $lastRateRequestResult;


    /**
     * Conversion constructor.
     *
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        $this->config = $config;

        // Prepare cache
        $this->cacheMatcher = new ExchangerRequestMatcher();

        $cacheStrategy = new DelegatingCacheStrategy(new NullCacheStrategy());

        if ($this->config['cache_time']) {
            $cacheInterface = (
                new ExchangerLaravelCacheInterface(
                    Cache::store($this->config['cache_store']),
                    $this->config['cache_prefix'] ?? ''
                )
            )->setDefaultCacheTime($this->config['cache_time']);

            $cacheStrategy->registerRequestMatcher($this->cacheMatcher, new PrivateCacheStrategy($cacheInterface));
        }

        $stack = HandlerStack::create();
        $stack->push(new CacheMiddleware($cacheStrategy), 'cache');

        // Initialize HTTP client.
        // $this->httpClient = Http::withOptions(['handler' => $stack])->buildClient();
        $this->httpClient = new Client(['handler' => $stack]);
    }


    /**
     * Convert between different currencies.
     *
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param $value
     * @param \DateTimeInterface|null $rateDate
     * @return float|int
     * @throws \Exchanger\Exception\ChainException
     * @throws \Throwable
     */
    public function convert(
        string $fromCurrency,
        string $toCurrency,
        $value,
        \DateTimeInterface $rateDate = null
    ) {
        $rate = $this->getRate($fromCurrency, $toCurrency, $rateDate);

        return $rate->getValue() * $value;
    }


    /**
     * Get the exchange rate information.
     *
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param \DateTimeInterface|null $rateDate
     * @return ExchangeRate
     * @throws \Exchanger\Exception\ChainException
     * @throws \Throwable
     */
    public function getRate(
        string $fromCurrency,
        string $toCurrency,
        \DateTimeInterface $rateDate = null
    ) : ExchangeRate {
        $currencyPair = new CurrencyPair(strtoupper($fromCurrency), strtoupper($toCurrency));

        $query = new ExchangeRateQueryBuilder((string) $currencyPair);

        if ($rateDate)
            $query = $query->setDate($rateDate);

        $rate = null;

        $this->lastRateRequestResult = $this->executeQuery($query->build());

        return $this->lastRateRequestResult;
    }


    /**
     * Obtain the last exchange rate result.
     *
     * @return ExchangeRate|null
     */
    public function getLastExchangeRateResult() : ?ExchangeRate {
        return $this->lastRateRequestResult;
    }


    /**
     * Execute a custom query.
     *
     * @param ExchangeRateQuery $query
     * @return ExchangeRate
     * @throws \Exchanger\Exception\ChainException
     * @throws \Throwable
     */
    public function executeQuery(ExchangeRateQuery $query): ExchangeRate
    {
        $this->attachAllIfNotRegistered();

        if ($query->getCurrencyPair()->isIdentical()) {
            return new \Exchanger\ExchangeRate(
                $query->getCurrencyPair(),
                1,
                Carbon::now()->toDate(),
                ''
            );
        }

        return $this->exchanger->getExchangeRate($query);
    }


    /**
     * Attach all the registered services into the configuration.
     *
     * @return $this
     * @throws \Throwable
     */
    public function attachAll() : ExchangerConverter
    {
        $this->attach(array_keys($this->config['services']));

        return $this;
    }


    /**
     * Detach all registered services.
     */
    public function detachAll() : ExchangerConverter
    {
        $this->serviceList = [];
        $this->exchanger = null;

        return $this;
    }


    /**
     * Attach one or more services.
     *
     * @param string ...$services
     * @return $this
     * @throws \Throwable
     */
    public function attach(...$services) : ExchangerConverter
    {
        $services = is_array($services[0]) ? $services[0] : $services;

        foreach ($services as $service) {

            // Ignore previous registered service.
            if (!empty($this->serviceList[$service]))
                continue;

            throw_if(
                !isset($this->config['services'][$service]),
                new ExchangerException("Exchange service $service was no registered into the configuration")
            );

            $this->serviceList[$service] = $this->config['services'][$service];
        }

        $this->createChainInstance();

        return $this;
    }


    /**
     * Detach one or more services.
     *
     * @param string ...$services
     * @return ExchangerConverter
     */
    public function detach(...$services) : ExchangerConverter
    {
        $services = is_array($services[0]) ? $services[0] : $services;

        foreach ($services as $service)
            unset($this->serviceList[$service]);

        $this->createChainInstance();

        return $this;
    }


    /**
     * Disable or enable cache.
     *
     * Cache will not work if cache_time is null or 0.
     *
     * @param bool $active
     * @return $this
     */
    public function setCacheUsage(bool $active) : ExchangerConverter {
        $this->cacheMatcher->setCacheStatus($active);

        return $this;
    }


    /**
     * Attach default services in case that any service was previously attached.
     *
     * @throws \Throwable
     */
    protected function attachAllIfNotRegistered() : void
    {
        if (empty($this->serviceList))
            $this->attachAll();

        throw_if(!$this->exchanger, new ExchangerException('Unable to register services'));
    }


    /**
     * Create the chain instance.
     */
    protected function createChainInstance() : void {
        $instances = [];

        foreach ($this->serviceList as $service => $options)
            $instances[] = new $service($this->httpClient, null, $options);

        $this->exchanger = new Chain($instances);
    }
}