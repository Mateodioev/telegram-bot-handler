<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

class ClosureMessageCommand extends MessageCommand
{

    private \Closure $command;

    private function __construct() {}

    public function setCommand(\Closure $fn): static
    {
        $this->command = $fn;
        return $this;
    }

    public static function fromClosure(\Closure $fn, string $name): static
    {
        $instance = new static;
        $instance->name = $name;
        return $instance->setCommand($fn);
    }

    public function handle(Api $bot, Context $context, array $args = [])
    {
        call_user_func($this->command, $bot, $context, $args);
    }
}
