<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Commands;

use Closure;

use function call_user_func;

/**
 * Create new MessageCommand from closure
 */
class ClosureMessageCommand extends MessageCommand
{
    private function __construct(
        private readonly Closure $command
    ) {
    }

    /**
     * Alias of fromClosure
     * @see fromClosure
     */
    public static function new(string $name, Closure $fn): static
    {
        return self::fromClosure($name, $fn);
    }

    public static function fromClosure(string $name, Closure $fn): static
    {
        $instance = new static($fn);
        $instance->name = $name;

        return $instance;
    }

    public function execute($args = [])
    {
        call_user_func($this->command, $this->api(), $this->ctx(), $args);
    }
}
