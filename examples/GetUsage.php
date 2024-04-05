<?php

declare(strict_types=1);

use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\TgHandler\Filters\FilterPrivateChat;

#[FilterPrivateChat]
class GetUsage extends MessageCommand
{
    protected string $name = 'usage';
    protected string $description = 'Get memory usage';

    /**
     * Run command
     * @throws Exception
     */
    public function execute(array $args = [])
    {
        $bytesToMb            = fn (int $bytes): float => \round($bytes / 1024 / 1024);
        $memory_usage         = $this->db()->get('memory_usage');
        $current_memory_usage = \memory_get_usage();

        $this->api()->replyToMessage(
            $this->ctx()->message(),
            \sprintf(
                "Start usage: %sMb\nCurrent Usage: %sMb\nDifference: %sMb",
                $bytesToMb($memory_usage),
                $bytesToMb($current_memory_usage),
                $bytesToMb($current_memory_usage - $memory_usage)
            ),
        );
    }

    public function onInvalidFilters(): ?bool
    {
        $this->api()->replyToMessage($this->ctx()->message(), 'Use this command in private chat');
        return false;
    }
}
