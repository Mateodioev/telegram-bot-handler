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

    /**
     * @inheritDoc
     */
    abstract public function execute(Api $bot, Context $context);

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