<?php
declare(strict_types=1);

namespace Juanparati\LaravelExchanger;


use Exchanger\Contract\ExchangeRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Traits\Conditionable;
use Juanparati\LaravelExchanger\Exceptions\ExchangerException;


/**
 * Class PendingConversion.
 *
 * Fluent builder for currency conversions.
 *
 * @package Juanparati\LaravelExchanger
 */
class PendingConversion
{

    use Conditionable;


    /**
     * Source currency code.
     *
     * @var string|null
     */
    protected ?string $from = null;


    /**
     * Target currency code.
     *
     * @var string|null
     */
    protected ?string $to = null;


    /**
     * Amount to convert.
     *
     * @var float
     */
    protected float $amount = 1.0;


    /**
     * Historical rate date.
     *
     * @var \DateTimeInterface|null
     */
    protected ?\DateTimeInterface $date = null;


    /**
     * Rounding precision.
     *
     * @var int|null
     */
    protected ?int $round = null;


    /**
     * Services to use for this conversion only.
     *
     * @var string[]
     */
    protected array $services = [];


    /**
     * Cache usage for this conversion only.
     *
     * @var bool
     */
    protected bool $useCache = true;


    /**
     * PendingConversion constructor.
     *
     * @param ExchangerConverter $converter
     */
    public function __construct(protected ExchangerConverter $converter) {}


    /**
     * Set the source currency.
     *
     * @param string $currency
     * @return $this
     */
    public function from(string $currency) : static {
        $this->from = $currency;

        return $this;
    }


    /**
     * Set the target currency.
     *
     * @param string $currency
     * @return $this
     */
    public function to(string $currency) : static {
        $this->to = $currency;

        return $this;
    }


    /**
     * Set the amount to convert.
     *
     * @param float|int $amount
     * @return $this
     */
    public function amount(float|int $amount) : static {
        $this->amount = (float) $amount;

        return $this;
    }


    /**
     * Use the historical rate at the given date.
     *
     * @param \DateTimeInterface|string $date
     * @return $this
     */
    public function date(\DateTimeInterface|string $date) : static {
        $this->date = is_string($date) ? Carbon::parse($date) : $date;

        return $this;
    }


    /**
     * Round the converted amount to the given precision.
     *
     * @param int $precision
     * @return $this
     */
    public function round(int $precision) : static {
        $this->round = $precision;

        return $this;
    }


    /**
     * Use only the given services for this conversion.
     *
     * @param string ...$services
     * @return $this
     */
    public function using(string ...$services) : static {
        $this->services = $services;

        return $this;
    }


    /**
     * Skip the cache for this conversion.
     *
     * @return $this
     */
    public function withoutCache() : static {
        $this->useCache = false;

        return $this;
    }


    /**
     * Obtain the exchange rate information.
     *
     * @return ExchangeRate
     * @throws ExchangerException
     * @throws \Throwable
     */
    public function rate() : ExchangeRate {
        throw_if(
            !$this->from || !$this->to,
            new ExchangerException('Both source and target currencies are required')
        );

        return $this->execute(
            fn () => $this->converter->getRate($this->from, $this->to, $this->date)
        );
    }


    /**
     * Obtain the converted amount.
     *
     * @return float
     * @throws ExchangerException
     * @throws \Throwable
     */
    public function get() : float {
        $value = $this->rate()->getValue() * $this->amount;

        return $this->round === null ? $value : round($value, $this->round);
    }


    /**
     * Run the callback applying the per-conversion settings and restore
     * the converter state afterwards (the converter is a shared singleton).
     *
     * @param callable $callback
     * @return mixed
     * @throws \Throwable
     */
    protected function execute(callable $callback) : mixed {
        $previousServices = $this->converter->getAttachedServices();
        $previousCache    = $this->converter->getCacheUsage();

        if ($this->services)
            $this->converter->detachAll()->attach($this->services);

        if (!$this->useCache)
            $this->converter->setCacheUsage(false);

        try {
            return $callback();
        } finally {
            if ($this->services) {
                $this->converter->detachAll();

                if ($previousServices)
                    $this->converter->attach($previousServices);
            }

            $this->converter->setCacheUsage($previousCache);
        }
    }
}
