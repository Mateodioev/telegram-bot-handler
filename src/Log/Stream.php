<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Log;

use SimpleLogger\streams\LogResult;

/**
 * Save messages with method push
 */
interface Stream
{
    /**
     * Push a message to the log stream
     * @param LogResult $message Message to log
     * @param ?string $level Log level
     */
    public function push(LogResult $message, ?string $level = null): void;
}
