<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\StringVars\Matcher;
use Mateodioev\TgHandler\Containers\Container;
use Mateodioev\TgHandler\Events\{EventType, abstractEvent};

abstract class Command extends abstractEvent implements CommandInterface
{
    public EventType $type = EventType::none;

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
     * Crete regex for use in match method
     */
    abstract protected function buildRegex(): Matcher;

    /**
     * Validate command
     * @return bool Return true if is valid command
     */
    abstract protected function match(string $text): bool;

    /**
     * Run when command filter is invalid but command is valid
     * @return bool true = execute handle method, false = not execute handle method but command is marked as execute, null = not execute nothing
     */
    public function onInvalidFilters(): ?bool
    {
        return null;
    }
}
