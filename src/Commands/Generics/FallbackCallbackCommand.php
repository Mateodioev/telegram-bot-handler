<?php

namespace Mateodioev\TgHandler\Commands\Generics;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

/**
 * Command to execute when cant find a valid command
 */
final class FallbackCallbackCommand implements FallbackCommand
{
    public function handle(Api $bot, Context $context): void
    {
        $command = $context->callbackQuery()->data() ?? '';
        $command = \explode(' ', $command)[0];

        $bot->answerCallbackQuery($context->callbackQuery()->id(), [
            'text' => 'Command "' . $command . '" not found',
            'show_alert' => true,
            'cache_time' => 300, // 5 minutes
        ]);
    }

    public function commands(): array
    {
        return [];
    }

    public function setCommands(array $commands): static
    {
        return $this;
    }
}
