<?php

namespace Mateodioev\TgHandler\Log;

/**
 * Save messages with method push
 */
interface Stream
{
    /**
     * @param string $message Message to log
     * @param ?string $level Log level
     */
    public function push(string $message, ?string $level = null): void;
}