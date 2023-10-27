<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\StringVars\Matcher;
use Mateodioev\TgHandler\Containers\Container;
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
        Container::singleton(static::class);
        return Container::make(static::class);
    }

    /**
     * Run command
     */
    abstract public function execute(array $args = []);

    /**
     * Crete regex for use in match method
     */
    abstract protected function buildRegex(): Matcher;

    /**
     * Validate command
     * @return bool Return true if is valid command
     */
    abstract protected function match(string $text): bool;
}
