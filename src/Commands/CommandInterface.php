<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Psr\Log\LoggerInterface;
use Closure;

interface CommandInterface
{
	/**
	 * The name of the telegram command
	 */
	public function getName(): string;

	/**
	 * Command aliases
	 */
	public function getAliases(): array;

    /**
     * Get command description
     */
    public function getDescription(): string;

    /**
     * Set command description
     */
    public function setDescription(string $description): void;

    /**
     * Set logger interface
     */
    public function setLogger(LoggerInterface $logger): static;

    /**
     * Get logger
     */
    public function getLogger(): LoggerInterface;

    /**
     * @param Closure[] $middlewares
     */
    public function setMiddlewares(array $middlewares): static;

    /**
     * @param Closure $middleware Middlewares receive 2 arguments: Context $context, Api $bot
     * @return CommandInterface
     */
    public function addMiddlewares(Closure $middleware): static;

    /**
     * @return Closure[]
     */
    public function getMiddlewares(): array;

    /**
     * @return bool Return true when command has middlewares
     */
    public function hasMiddlewares(): bool;

	/**
	 * Run command
     * @param Api $bot Telegram bot api
     * @param Context $context Command context
     * @param array $args Middlewares results
	 */
	public function execute(Api $bot, Context $context, array $args = []);
}
