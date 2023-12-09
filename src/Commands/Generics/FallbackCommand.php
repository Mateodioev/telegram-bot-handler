<?php

namespace Mateodioev\TgHandler\Commands\Generics;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\Command;
use Mateodioev\TgHandler\Context;

interface FallbackCommand
{
    public function handle(Api $bot, Context $context): void;

    /**
     * @return Command[]
     */
    public function commands(): array;

    /**
     * @param Command[] $commands
     */
    public function setCommands(array $commands): static;
}
