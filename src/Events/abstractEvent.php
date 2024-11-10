<?php

declare (strict_types=1);

namespace Mateodioev\TgHandler\Events;

use Closure;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\BotException;
use Mateodioev\TgHandler\Db\{DbInterface, PrefixDb};
use Mateodioev\TgHandler\Filters\{Filter, FilterCollection};
use Mateodioev\TgHandler\{Bot, Context, Middleware\ClosureMiddleware, Middleware\Middleware};
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Revolt\EventLoop;

use Throwable;

use function Amp\delay;
use function class_exists;
use function explode;
use function is_callable;

abstract class abstractEvent implements EventInterface
{
    public EventType $type;

    protected string $description = '';
    protected LoggerInterface $logger;
    protected DbInterface $db;
    protected Api $botApi;
    protected Context $botContext;
    /**
     * @var array<class-string<Middleware>|Middleware|Closure>
     */
    protected array $middlewares = [];

    /**
     * @var bool Transform middlewares to Middleware instances
     */
    private bool $transformMiddlewares = true;

    /** @var Filter[] Event filters */
    private ?array $filters = null;
    private ?DbInterface $privateDb = null;

    public function type(): EventType
    {
        return $this->type;
    }

    /**
     * @internal
     */
    public function setVars(Api $bot, Context $ctx): static
    {
        $this->botApi = $bot;
        $this->botContext = $ctx;
        return $this;
    }

    public function api(): Api
    {
        return $this->botApi;
    }

    /**
     * @internal
     * @api
     */
    public function setApi(Api $api): static
    {
        $this->botApi = $api;
        return $this;
    }

    public function ctx(): Context
    {
        return $this->botContext;
    }

    /**
     * @internal
     * @api
     */
    public function setCtx(Context $ctx): static
    {
        $this->botContext = $ctx;
        return $this;
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
     * @see delay
     */
    public function sleep(float $seconds): void
    {
        delay($seconds);
    }

    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger();
    }

    /**
     * @internal
     */
    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function db(): DbInterface
    {
        return $this->db;
    }

    /**
     * @internal
     */
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

        $currentClassName = strtolower((new ReflectionClass($this))->getShortName()) . '.';
        $this->privateDb = new PrefixDb($this->db(), $currentClassName);

        return $this->privateDb;
    }

    public function hasMiddlewares(): bool
    {
        return !empty($this->middlewares());
    }

    /**
     * Get all middlewares
     *
     * @return Middleware[]
     */
    public function middlewares(): array
    {
        if ($this->transformMiddlewares === false) {
            return $this->middlewares;
        }
        $middlewares = [];
        foreach ($this->middlewares as $i => $middleware) {
            if ($middleware instanceof Closure) {
                $middlewares[(string) $i] = ClosureMiddleware::create($middleware)->withId($i);
                continue;
            }
            if (is_string($middleware)) {
                $middleware = $this->createMiddlewareFromString($middleware);
                $middlewares[$middleware->name()] = $middleware;
                continue;
            }
            if ($middleware instanceof Middleware) {
                $middlewares[$middleware->name()] = $middleware;
                continue;
            }
            // Keep everything else (or maybe throw an exception?)
            $middlewares[(string) $i] = $middleware;
        }
        $this->middlewares = $middlewares;
        $this->transformMiddlewares = false;
        return $this->middlewares;
    }

    private function createMiddlewareFromString(string $strMiddleware): Middleware
    {
        [$className, $parts] = explode(':', "$strMiddleware:"); // add ":" to avoid errors

        $existsClass = class_exists($className);
        if (!$existsClass && is_callable($className)) {
            // Parts are not needed for closures
            return ClosureMiddleware::create($className);
        }
        if ($existsClass === false) {
            throw new BotException("Middleware class $className not found");
        }

        $parts = explode(',', $parts);

        if (empty($parts)) {
            return new $className();
        }

        try {
            return new $className(...$parts);
        } catch (Throwable $th) {
            throw new BotException("Error creating middleware $className: " . $th->getMessage());
        }
    }

    /**
     * @throws ReflectionException
     */
    public function hasFilters(): bool
    {
        // Filter already mapped
        if ($this->filters !== null) {
            return empty($this->filters) === false;
        }

        $this->filters();
        return empty($this->filters) === false;
    }

    /**
     * Get event filters
     * @throws ReflectionException
     * @return Filter[]
     */
    private function filters(): array
    {
        $this->filters ??= self::mapFilters($this);
        return $this->filters;
    }

    /**
     * @internal
     * @throws ReflectionException
     */
    public function validateFilters(): bool
    {
        // No need validation
        if ($this->hasFilters() === false) {
            return true;
        }

        $filterCollection = new FilterCollection(...$this->filters());

        return $filterCollection->apply($this->ctx());
    }

    public function addMiddleware(Closure|Middleware $middleware): static
    {
        $this->transformMiddlewares = true;
        $middleware = $middleware instanceof Closure ? ClosureMiddleware::create($middleware) : $middleware;
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function setMiddlewares(array $middlewares): static
    {
        $this->transformMiddlewares = true;
        $this->middlewares = [ ...$this->middlewares(), ...$middlewares];
        return $this;
    }

    /**
     * @internal
     */
    public function isValid(): bool
    {
        return $this->ctx()->eventType() == $this->type();
    }

    /**
     * Run a task in background
     */
    public function background(Closure $callback): void
    {
        EventLoop::defer($callback);
    }

    /**
     * Map Filters of any subclass of EventInterface
     * @throws ReflectionException
     * @return Filter[]
     */
    private static function mapFilters(string | object $class): array
    {
        $attributes = (new ReflectionClass($class))->getAttributes();
        $filters = [];

        foreach ($attributes as $attribute) {
            if (is_subclass_of($attribute->getName(), Filter::class) === false) {
                continue;
            }

            $filters[] = $attribute->newInstance();
        }

        return $filters;
    }

    public function stop(): void
    {
        Bot::terminate();
    }
}
