<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Events\abstractEvent;

abstract class Command extends abstractEvent implements CommandInterface
{

    protected string $name = '';
    protected array $alias = [];

    /**
     * Get command name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get command aliases
     */
    public function getAliases(): array
    {
        return $this->alias;
    }

	/**
	 * Get new instance of the command
	 */
	public static function get(): static
	{
		return new static;
	}

    public function isValid(Api $bot, Context $context): bool
    {
        return true; // default for command events
    }

    /**
     * Run command
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