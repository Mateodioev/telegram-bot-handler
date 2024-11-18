<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Log;

use InvalidArgumentException;
use SimpleLogger\Formatters\{Formatter, PrettyConsoleFormatter};
use SimpleLogger\streams\LogResult;

/**
 * Print logs in terminal
 */
final class TerminalStream implements Stream
{
    private ResourceStream $stdout;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        $stdout = STDOUT,
        Formatter $formatter = new PrettyConsoleFormatter(),
    ) {
        if (!$stdout) {
            $stdout = STDOUT;
        }

        $this->stdout = new ResourceStream($stdout, $formatter);
    }

    public function push(LogResult $message, ?string $level = null): void
    {
        $this->stdout->push($message, $level);
    }

    public function __destruct()
    {
        $this->stdout->close();
    }
}
