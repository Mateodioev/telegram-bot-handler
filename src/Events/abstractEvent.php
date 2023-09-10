<?php

namespace Mateodioev\TgHandler\Events;

use Closure;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Db\DbInterface;
use Mateodioev\TgHandler\Db\PrefixDb;
use Mateodioev\TgHandler\Filters\Filter;
use Mateodioev\TgHandler\Filters\FilterCollection;
use Psr\Log\LoggerInterface;
use ReflectionClass;

use function array_merge;

abstract class abstractEvent implements EventInterface
{
    public EventType $type;

    protected string $description = '';
    protected LoggerInterface $logger;
    protected DbInterface $db;
    protected array $middlewares = [];

    /** @var Filter[] Event filters */
    private ?array $filters = null;
    private ?DbInterface $privateDb = null;

    public function type(): EventType
    {
        return $this->type;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Async sleep
     * 
     * @param float $seconds Number of seconds to sleep
     * @see \Amp\delay()
     */
    public function sleep(float $seconds): void
    {
        \Amp\delay($seconds);
    }

    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger();
    }

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function db(): DbInterface
    {
        return $this->db;
    }

    public function setDb(DbInterface $db): static
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Return a db with a prefix key
     */
    public function privateDb(): DbInterface
    {
        if ($this->privateDb) {
            return $this->privateDb;
        }

        $currentClassName = \strtolower((new ReflectionClass($this))->getShortName()) . '.';
        $this->privateDb  = new PrefixDb($this->db(), $currentClassName);

        return $this->privateDb;
    }

    public function hasMiddlewares(): bool
    {
        return !empty($this->middlewares());
    }

    public function middlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @throws \ReflectionException
     */
    public function hasFilters(): bool
    {
        // Filter already mapped
        if ($this->filters !== null)
            return empty($this->filters) === false;

        $this->filters();
        return empty($this->filters) === false;
    }

    /**
     * @throws \ReflectionException
     */
    public function filters(): array
    {
        $this->filters ??= self::mapFilters($this);
        return $this->filters;
    }

    /**
     * @throws \ReflectionException
     */
    public function validateFilters(Context $ctx): bool
    {
        // No need validation
        if ($this->hasFilters() === false) {
            return true;
        }

        $filterCollection = new FilterCollection(...$this->filters());

        if ($filterCollection->apply($ctx) === false) {
            return false;
        }

        return true;
    }

    public function addMiddleware(Closure $middleware): static
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function setMiddlewares(array $middlewares): static
    {
        $this->middlewares = array_merge($this->middlewares(), $middlewares);
        return $this;
    }

    public function isValid(Api $bot, Context $context): bool
    {
        return $context->eventType() == $this->type();
    }

    /**
     * Map Filters of any subclass of EventInterface
     * @return Filter[]
     * @throws \ReflectionException
     */
    public static function mapFilters(string|object $class): array
    {
        $attributes = (new ReflectionClass($class))->getAttributes();
        $filters = [];

        foreach ($attributes as $attribute) {
            if (is_subclass_of($attribute->getName(), Filter::class) === false)
                continue;

            $filters[] = $attribute->newInstance();
        }

        return $filters;
    }
}
