<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\TgHandler\{Bot, Context, BotException};

/**
 * Throw this exception to stop command execution
 */
class StopCommand extends BotException
{
    /**
     * @var callable|Closure|null Custom handler for StopCommand, receive same arguments as StopCommand::handler
     * @see StopCommand::handler
     */
    public static mixed $handler = null;
    public static string $parseMode = 'html';

    /**
     * @throws BotException
     */
    public static function handler(StopCommand $e, Bot $bot, Context $ctx): void
    {
        if (empty($e->getMessage()))
            return;

        if (is_callable(self::$handler)) {
            call_user_func(self::$handler, $e, $bot, $ctx);
        } else {
            $bot->getLogger()->notice('StopCommand: ' . $e->getMessage());
            $bot->getApi()->sendMessage($ctx->getChatId(), $e->getMessage(), ['parse_mode' => self::$parseMode]);
        }
    }
}