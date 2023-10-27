<?php

namespace Mateodioev\TgHandler\Events;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Db\DbInterface;
use Psr\Log\LoggerInterface;

/**
 * This interface represents an event
 */
interface EventInterface
{
    /**
     * Get event type
     */
    public function type(): EventType;

    /**
     * Set api and context vars
     */
    public function setVars(Api $bot, Context $ctx): static;

    /**
     * Get description
     */
    public function description(): string;

    /**
     * Set description
     */
    public function setDescription(string $description): void;

    /**
     * Get logger
     */
    public function logger(): LoggerInterface;

    /**
     * @see static::logger
     * @deprecated v3.5.0 Use `static::logger`
     */
    public function getLogger(): LoggerInterface;

    /**
     * Set logger
     */
    public function setLogger(LoggerInterface $logger): static;

    /**
     * Get db to storage data
     */
    public function db(): DbInterface;

    /**
     * Get db to storage data
     */
    public function setDb(DbInterface $db): static;

    /**
     * Return true if event has middlewares
     */
    public function hasMiddlewares(): bool;

    /**
     * Get middlewares
     */
    public function middlewares(): array;

    /**
     * Return true if event has Attributes as Filters
     */
    public function hasFilters(): bool;

    /**
     * Validates all filters, return `false` if any of them fail
     */
    public function validateFilters(): bool;

    /**
     * Add single middleware
     */
    public function addMiddleware(\Closure $middleware): static;

    /**
     * @param Closure[] $middlewares
     */
    public function setMiddlewares(array $middlewares): static;

    /**
     * Return true if the event is valid
     */
    public function isValid(): bool;

    /**
     * Run event
     * @param Api $bot Telegram bot api
     * @param Context $context Update context
     * @param array $args Middlewares results
     */
    public function execute(array $args = []);

    /**
     * Get Telegram bot api instance
     */
    public function api(): Api;

    /**
     * Get Context
     */
    public function ctx(): Context;
}
