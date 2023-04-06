<?php

namespace Mateodioev\TgHandler\Events;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
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
     */
    public function getLogger(): LoggerInterface;

    /**
     * Set logger
     */
    public function setLogger(LoggerInterface $logger): static;

    /**
     * Return true if event has middlewares
     */
    public function hasMiddlewares(): bool;

    /**
     * Get middlewares
     */
    public function middlewares(): array;

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
    public function isValid(Api $bot, Context $context): bool;

    /**
     * Run event
     * @param Api $bot Telegram bot api
     * @param Context $context Update context
     * @param array $args Middlewares results
     */
    public function execute(Api $bot, Context $context, array $args = []);
}
