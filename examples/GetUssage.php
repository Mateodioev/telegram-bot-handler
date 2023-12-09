<?php

declare(strict_types=1);

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Filters\FilterPrivateChat;

#[FilterPrivateChat]
class GetUssage extends MessageCommand
{
    protected string $name = 'usage';
    protected string $description = 'Get memory usage';

    /**
     * Run command
     * @throws Exception
     */
    public function handle(Api $bot, Context $context, array $args = [])
    {
        $bytesToMb            = fn (int $bytes): float => \round($bytes / 1024 / 1024);
        $memory_usage         = $this->db()->get('memory_usage');
        $current_memory_usage = \memory_get_usage();

        $bot->replyToMessage(
            $context->message(),
            \sprintf(
                "Start usage: %sMb\nCurrent Usage: %sMb\nDifference: %sMb",
                $bytesToMb($memory_usage),
                $bytesToMb($current_memory_usage),
                $bytesToMb($current_memory_usage - $memory_usage)
            ),
        );
    }
}
