<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Psr\Log\LoggerInterface;

abstract class Command implements CommandInterface
{

    protected string $name = '';
    protected array $alias = [];
    protected string $description;
    protected array $middlewares = [];

    protected LoggerInterface $logger;


    public function getName(): string
    {
        return $this->name;
    }

    public function getAliases(): array
    {
        return $this->alias;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set command description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

	/**
	 * Get new instance of the command
	 */
	public static function get(): static
	{
		return new static;
	}

    public function setMiddlewares(array $middlewares): static
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    public function addMiddlewares(\Closure $middleware): static
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function hasMiddlewares(): bool
    {
        return !empty($this->middlewares);
    }

    /**
     * @inheritDoc
     */
    public function isValid(Api $bot, Context $context): bool
    {
        return true; // default for command events
    }

    /**
     * @inheritDoc
     */
    abstract public function execute(Api $bot, Context $context, array $args = []);

    /**
     * Crete regex for use in match method
     */
    abstract protected function buildRegex(): string;

    /**
     * Validate command
     * @return bool Return true if is valid command
     */
    abstract public function match(string $text): bool;
}