<?php

namespace Mateodioev\TgHandler\Events;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Db\DbInterface;
use Psr\Log\LoggerInterface;

use function array_merge;

abstract class abstractEvent implements EventInterface
{
    public EventType $type;

    protected string $description = '';
    protected LoggerInterface $logger;
    protected DbInterface $db;
    protected array $middlewares = [];

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

    public function hasMiddlewares(): bool
    {
        return !empty($this->middlewares());
    }

    public function middlewares(): array
    {
        return $this->middlewares;
    }

    public function addMiddleware(\Closure $middleware): static
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
}