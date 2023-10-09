<?php

namespace Mateodioev\TgHandler\Log;

use Mateodioev\TgHandler\BotException;
use function fwrite;

/**
 * Print logs in terminal
 */
final class TerminalStream implements Stream
{
    private ResourceStream $stdout;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct($stdout = STDOUT)
    {
        if (!$stdout)
            $stdout = STDOUT;

        $this->stdout = new ResourceStream($stdout);
    }

    public function push(string $message, ?string $level = null): void
    {
        $this->stdout->push($message, $level);
    }

    public function __destruct()
    {
        $this->stdout->close();
    }
}
